<?php

namespace App\Http\Services;

use App\Http\Repositories\AffiliateRepository;
use App\Jobs\DistributeWithdrawalReferralBonus;
use App\Jobs\MailSend;
use App\Jobs\Withdrawal;
use App\Model\Coin;
use App\Model\CoWalletWithdrawApproval;
use App\Model\DepositeTransaction;
use App\Model\TempWithdraw;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletCoUser;
use App\Model\WalletNetwork;
use App\Model\WithdrawHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

use function PHPUnit\Framework\isNull;

class TransService
{
    protected $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    private function generate_email_verification_key()
    {
        do {
            $key = Str::random(60);
        } while (User::where('email_verified', $key)->count() > 0);

        return $key;
    }

    // make withdrawal data
    private function make_withdrawal_data($data, $address_type, $fees, $trans_id, $receiverWallet = null)
    {
        return [
            'wallet_id' => $data['wallet']->id,
            'address' => $data['address'],
            'amount' => $data['amount'],
            'address_type' => $address_type,
            'fees' => $fees,
            'coin_type' => $data['wallet']->coin_type,
            'transaction_hash' => $trans_id,
            'confirmations' => 0,
            'status' => STATUS_PENDING,
            'message' => $data['note'],
            'receiver_wallet_id' => is_null($receiverWallet) ? 0 : $receiverWallet->id,
            'user_id' => $data['user']->id,
            'network_type' => $data['network_type'] ?? '',
            'memo' => $data['memo'] ? $data['memo'] : ''
        ];
    }
    // withdrawal process from job
    public function send($data)
    {
        $responseWithdrawal = responseData(false, __('Invalid request'));
        try {
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $data['wallet_id'], 'wallets.user_id' => $data['user']->id])
                ->select(
                    'wallets.*',
                    'coins.name as coin_name',
                    'coins.status as coin_status',
                    'coins.status as coin_status',
                    'coins.is_withdrawal',
                    'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal',
                    'coins.withdrawal_fees',
                    'coins.max_send_limit',
                    'coins.withdrawal_fees_type',
                    'coins.network'
                )
                ->first();
            $user = $wallet->user;

            $coin_name = strtolower($wallet->coin_name);
            //            $sender_wallet_address = WalletAddressHistory::where('wallet_id',$wallet->id)->first()->address;
            if ($wallet) {
                if ($wallet->coin_type == COIN_USDT && $wallet->network == COIN_PAYMENT) {
                    $checkNetwork = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $data['network_type']])->first();
                    if (empty($checkNetwork)) {
                        storeException('wallet ', $wallet);
                        storeException('withdrawal usdt network not found', $data['network_type']);
                        return responseData(false, __('Selected Coin API is not found'));
                    }
                }
                $checkValidate = $this->checkWithdrawalValidation($data['address'], $data['amount'], $data['user'], $wallet);
                if ($checkValidate['success'] ==  false) {
                    $response = [
                        'success' => false,
                        'message' => $checkValidate['message'],
                        'data' => ''
                    ];
                    storeBotException('send job', json_encode($response));
                    return $response;
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found'),
                    'data' => ''
                ];
                storeBotException('send job', json_encode($response));
                return $response;
            }
            $trans_id = Str::random(32); // we make this same for deposit and withdrawl
            DB::beginTransaction();
            try {
                $walletAddress = $this->isInternalAddress($data['address']);
                if (empty($walletAddress)) {
                    $receiverWallet = null;
                    $receiverUser = null;
                    $address_type = ADDRESS_TYPE_EXTERNAL;
                    $fees = $checkValidate['data']['fees'];
                } else {
                    $fees = 0;
                    $receiverWallet = $walletAddress->wallet;
                    $receiverUser = $walletAddress->wallet->user;
                    $address_type = ADDRESS_TYPE_INTERNAL;
                    if ($user->id == $receiverUser->id) {
                        storeBotException('send ', 'You can not send to your own wallet!');
                        return ['success' => false, 'message' => __('You can not send to your own wallet!')];
                    }
                    if ($wallet->coin_type != $walletAddress->wallet->coin_type) {
                        storeBotException('send', 'You can not make withdrawal, because wallet coin type is mismatched. Your wallet coin type and withdrawal address coin type should be same.');
                        return ['success' => false, 'message' => __('You can not make withdrawal, because wallet coin type is mismatched. Your wallet coin type and withdrawal address coin type should be same.')];
                    }
                }
                if (($data['amount'] + $fees) > $wallet->balance) {

                    return ['success' => false, 'message' => 'Insufficient Balance!'];
                }
                $sendAmount = $data['amount'] + $fees;
                $wallet->decrement('balance', $sendAmount);
                $data['wallet'] = $wallet;

                $transaction = WithdrawHistory::create($this->make_withdrawal_data($data, $address_type, $fees, $trans_id, $receiverWallet));
                storeBotException('send job withdrawal data', json_encode($transaction));

                if ($address_type == ADDRESS_TYPE_INTERNAL) {

                    if (checkCryptoAdminApproval($data['amount'], $wallet->coin_id)) {
                    } else {
                        $transaction->status = STATUS_SUCCESS;
                        $transaction->save();
                    }

                    if (!empty($receiverWallet)) {
                        $receive_tr =  DepositeTransaction::create($this->makeDepositData($data, $address_type, $fees, $trans_id, $receiverWallet->id));

                        if (checkCryptoAdminApproval($data['amount'], $wallet->coin_id)) {
                            storeBotException('internal withdrawal process ', 'goes to admin approval');
                            $responseWithdrawal = responseData(true, __('Internal withdrawal process goes to admin approval'));
                        } else {
                            $receive_tr->status = STATUS_SUCCESS;
                            $receive_tr->save();
                            $receiverWallet->increment('balance', $data['amount']);

                            $responseWithdrawal = responseData(true, __('Internal withdrawal process success'));
                        }
                    }
                } else {
                    storeBotException('withdrawal process', 'external withdrawal');
                    if (checkCryptoAdminApproval($data['amount'], $wallet->coin_id) || defined("IS_PUBLIC_API")) {
                        storeBotException('external withdrawal process ', 'goes to admin approval');
                        $responseWithdrawal = responseData(true, __('External withdrawal process goes to admin approval'));
                    } else {

                        $externalProcess = $this->acceptPendingExternalWithdrawal($transaction, "");
                        if ($externalProcess['success'] == false) {
                            storeException('external withdrawal process failed', json_encode($externalProcess));
                            storeBotException(' external withdrawal', 'so its goes to admin approval automatically');
                            $transaction->update(['automatic_withdrawal' => 'failed']);
                        } else {
                            storeBotException('external withdrawal process ', 'end. withdrawal successfully');
                        }
                        $responseWithdrawal = $externalProcess;
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                storeException('coin send exception ', $e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            storeException('coin send exception ', $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
        DB::commit();
        return $responseWithdrawal;
    }


    //make deposit data
    public function makeDepositData($data, $address_type, $fees, $trans_id, $receiverWalletId)
    {
        return [
            'address' => $data['address'],
            'address_type' => $address_type,
            'amount' => $data['amount'],
            'fees' => $fees,
            'coin_type' => $data['wallet']->coin_type,
            'transaction_id' => $trans_id,
            'confirmations' => 0,
            'status' => STATUS_PENDING,
            'sender_wallet_id' => $data['wallet']->id,
            'receiver_wallet_id' => $receiverWalletId,
            'network_type' => $data['network_type'] ?? ''
        ];
    }
    // check internal address
    private function isInternalAddress($address)
    {
        $checkAddress = WalletAddressHistory::where('address', $address)->with('wallet')->first();
        if ($checkAddress) {
            return $checkAddress;
        } else {
            return WalletNetwork::where('address', $address)->with('wallet')->first();
        }
    }

    // cancel transaction
    private function _cancelTransaction($user, $wallet, $address, $amount, $pendingTransaction)
    {
        if (!empty($pendingTransaction)) {
            $pendingTransaction->status = STATUS_REJECTED;
            $pendingTransaction->update();
        }
        //  $mailService = app(MailService::class);
        $userName = $user->first_name . ' ' . $user->last_name;
        $userEmail = $user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => __('Send coin failure'), 'companyName' => $companyName]);
        $data['user'] = $user;
        $data['amount'] = $amount;
        $data['address'] = $address;
        $data['wallet'] = $wallet;
        //  $mailService->send('email.send_coin_failure', $data, $userEmail, $userName, $subject);
    }





    private function calculate_fees($amount)
    {
        return $amount;
    }


    private function sendTransactionMail($sender_user, $mailTemplet, $receiver_user, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['anotherUser'] = $receiver_user;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendExternalTransactionMail($sender_user, $mailTemplet, $address, $amount, $emailSubject)
    {
        $mailService = app(MailService::class);
        $userName = $sender_user->first_name . ' ' . $sender_user->last_name;
        $userEmail = $sender_user->email;
        $companyName = isset($default['company']) && !empty($default['company']) ? $default['company'] : __('Coin Wallet');
        $subject = __(':emailSubject | :companyName', ['emailSubject' => $emailSubject, 'companyName' => $companyName]);
        $data['data'] = $sender_user;
        $data['address'] = $address;
        $data['amount'] = $amount;
        $mailService->send($mailTemplet, $data, $userEmail, $userName, $subject);
    }

    private function sendVerificationSms($phone, $randno)
    {
        $smsText = 'Your ' . allsetting()['app_title'] . ' verification code is here ' . $randno;
        app(SmsService::class)->send($phone, $smsText);
    }

    // user deposit history
    public function depositTransactionHistories($user_id = null, $status = null, $search = null, $order_by = null)
    {
        $histories = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id')
            ->select('wallets.*', 'deposite_transactions.*')
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('wallets.user_id', $user_id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('deposite_transactions.status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('deposite_transactions.address', 'like', "%$search%")
                        ->orWhere('deposite_transactions.transaction_id', 'like', "%$search%")
                        ->orWhere('deposite_transactions.coin_type', 'like', "%$search%")
                        ->orWhere('deposite_transactions.amount', 'like', "%$search%");
                });
            })
            ->when(!empty($order_by['column_name']) && !empty($order_by['order_by']), function ($query) use ($order_by) {
                $withdraw_columns = ['created_at', 'address', 'amount', 'fees', 'coin_type'];
                if (in_array($order_by['column_name'], $withdraw_columns)) {
                    return $query->orderBy("deposite_transactions.$order_by[column_name]", $order_by['order_by']);
                }
                return $query->orderBy("wallets.$order_by[column_name]", $order_by['order_by']);
            });

        return $histories;
    }

    // user withdrawal history
    public function withdrawTransactionHistories($user_id = null, $status = null, $search = null, $order_by = null)
    {
        $histories = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->select('wallets.*', 'withdraw_histories.*')
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('wallets.user_id', $user_id);
            })
            ->when($status, function ($query) use ($status) {
                return $query->where('withdraw_histories.status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('withdraw_histories.address', 'like', "%$search%")
                        ->orWhere('withdraw_histories.transaction_hash', 'like', "%$search%")
                        ->orWhere('withdraw_histories.coin_type', 'like', "%$search%")
                        ->orWhere('withdraw_histories.amount', 'like', "%$search%");
                });
            })
            ->when(!empty($order_by['column_name']) && !empty($order_by['order_by']), function ($query) use ($order_by) {
                $withdraw_columns = ['created_at', 'address', 'amount', 'fees', 'coin_type'];
                if (in_array($order_by['column_name'], $withdraw_columns)) {
                    return $query->orderBy("withdraw_histories.$order_by[column_name]", $order_by['order_by']);
                }
                return $query->orderBy("wallets.$order_by[column_name]", $order_by['order_by']);
            });

        return $histories;
    }

    public function isAllApprovalDoneForCoWalletWithdraw($tempWithdraw)
    {
        if (empty($tempWithdraw)) {
            Log::warning('Empty temp withdrawal.');
            return ['success' => false, 'message' => __('Invalid withdrawal.')];
        }
        $response = $this->approvalCounts($tempWithdraw);
        if ($response['alreadyApprovedUserCount'] >= $response['requiredUserApprovalCount']) {
            $tempWithdraw->status = STATUS_ACCEPTED;
            try {
                if (!$tempWithdraw->save()) throw new \Exception(__('Temp withdraw status success save failed'));
                return ['success' => true, 'message' => ''];
            } catch (\Exception $e) {
                Log::warning($e->getMessage());
                return ['success' => false, 'message' => $e->getMessage()];
            }
        } else return ['success' => false, 'message' => __('Not enough approval done yet.')];
    }

    public function approvalCounts($tempWithdraw)
    {
        $userPercentageForApproval = settings(CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG);
        $userPercentageForApproval = !empty($userPercentageForApproval) ? $userPercentageForApproval : 60;
        $coUserCount = WalletCoUser::where(['wallet_id' => $tempWithdraw->wallet_id])->count();
        $requiredUserApprovalCount = ceil($coUserCount * ($userPercentageForApproval / 100.0));
        $alreadyApprovedUserCount = CoWalletWithdrawApproval::where(['temp_withdraw_id' => $tempWithdraw->id])->count();
        return ['requiredUserApprovalCount' => $requiredUserApprovalCount, 'alreadyApprovedUserCount' => $alreadyApprovedUserCount];
    }

    // check withdrawal validation
    public function checkWithdrawalValidation($address, $amount, $user, $wallet)
    {
        $fees = check_withdrawal_fees($amount, $wallet->withdrawal_fees, $wallet->withdrawal_fees_type);
        $data = [
            'data' => [
                'fees' => $fees,
                'amount' => $amount,
                'fees_percentage' => $wallet->withdrawal_fees,
                'fees_type' => $wallet->withdrawal_fees_type,
            ],
            'success' => true,
            'message' => __('Success')
        ];
        $walletAddress = $this->isInternalAddress($address);
        if ($walletAddress) {
            if ($walletAddress->wallet->user_id == $wallet->user_id) {
                $data = [
                    'data' => [],
                    'success' => false,
                    'message' => __('You can not send to your own wallet!')
                ];
                return $data;
            }
            if ($walletAddress->wallet->coin_type != $wallet->coin_type) {
                $data = [
                    'data' => [],
                    'success' => false,
                    'message' => __('Both wallet coin type should be same')
                ];
                return $data;
            }
        }
        $checkStatus = check_coin_status($wallet, $user->id, $amount, $fees);
        if ($checkStatus['success'] == false) {
            return $checkStatus;
        }
        return $data;
    }

    // admin pending withdrawal accept process

    public function acceptPendingExternalWithdrawal($transaction, $adminId = null)
    {
        if (!empty($adminId)) {
            storeException('acceptPendingExternalWithdrawal', 'accept process started from admin end');
        } else {
            storeException('acceptPendingExternalWithdrawal', 'withdrawal process started from user end');
        }
        try {
            $coin = $transaction->coin;
            $currency =  !empty($transaction->network_type) ? $transaction->network_type : $transaction->coin_type;

            if ($coin->network == COIN_PAYMENT) {

                $coinPayment = new CoinPaymentsAPI();
                $auto_confirm = FALSE;
                if (isset(allsetting()['coin_payment_withdrawal_email']) && allsetting()['coin_payment_withdrawal_email'] == STATUS_ACTIVE) {
                    $auto_confirm = TRUE;
                }
                $networkFees = network_fees_coinPayment($currency);
                $amountWithNetworkFees = bcaddx($transaction->amount, $networkFees, 8);
                storeException('CreateWithdrawal amount -> ', $transaction->amount);
                storeException('CreateWithdrawal network fees -> ', $networkFees);
                storeException('CreateWithdrawal amount with network fees -> ', $amountWithNetworkFees);
                $response = $coinPayment->CreateWithdrawal($amountWithNetworkFees, $currency, $transaction->address, $auto_confirm, '', $transaction->memo);

                if (is_array($response) && isset($response['error']) && ($response['error'] == 'ok')) {
                    $transaction->transaction_hash = $response['result']['id'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->updated_by = $adminId;
                    if (empty($adminId)) {
                        $transaction->automatic_withdrawal = 'success';
                    }
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $response['error']];
                }
            } elseif ($coin->network == BITCOIN_API) {
                $result = $this->external_transfer_using_coin_api($currency, $transaction->address, $transaction->amount, Auth::id(), true, $transaction->user_id);

                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['transaction_id'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->updated_by = $adminId;
                    if (empty($adminId)) {
                        $transaction->automatic_withdrawal = 'success';
                    }
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } elseif ($coin->network == BITGO_API) {
                $result = $this->sendCoinWithBitgo($transaction);
                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['data'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->updated_by = $adminId;
                    if (empty($adminId)) {
                        $transaction->automatic_withdrawal = 'success';
                    }
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');

                    return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } elseif ($coin->network == ERC20_TOKEN || $coin->network == BEP20_TOKEN || $coin->network == TRC20_TOKEN || $coin->network == MATIC_TOKEN) {
                $result = $this->sendCoinWithERC20($transaction);
                if ($result['success'] == true) {
                    $transaction->transaction_hash = $result['data']['transaction_id'];
                    $transaction->used_gas = $result['data']['used_gas'];
                    $transaction->status = STATUS_SUCCESS;
                    $transaction->updated_by = $adminId;
                    if (empty($adminId)) {
                        $transaction->automatic_withdrawal = 'success';
                    }
                    $transaction->update();
                    dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');
                    if (empty($adminId)) {
                        return ['success' => true, 'message' => __('User withdrawal processed successfully.')];
                    } else {
                        return ['success' => true, 'message' => __('Pending withdrawal accepted Successfully.')];
                    }
                } else {
                    return ['success' => false, 'message' => $result['message']];
                }
            } else {
                return ['success' => false, 'message' => __('No Api found')];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }


    // external transfer by using bit coin api
    public function external_transfer_using_coin_api($coinType, $address, $amount, $authId, $isAdmin, $user_id)
    {
        try {
            $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $coinType])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $api =  new BitCoinApiService($coin->coin_api_user, decryptId($coin->coin_api_pass), $coin->coin_api_host, $coin->coin_api_port);
                $response = $api->verifyAddress($address);
                storeException('address validation check', $response);
                if (!$response) {
                    return ['success' => false, 'message' => __('Not a valid address!')];
                }

                $adminId = null;
                $userId = $user_id;
                if ($isAdmin) {
                    $adminId = $authId;
                } else {
                    $userId = $authId;
                }

                $transaction_id = $api->sendToAddress($address, $amount, $userId, $adminId);
                storeBotException("transaction_id ", $transaction_id);
                if ($transaction_id) {
                    return [
                        'success' => true,
                        'message' => __('Transfer successfully!'),
                        'transaction_id' => $transaction_id
                    ];
                }
                return [
                    'success' => false,
                    'message' => __('Failed to send coin!'),
                    'nodeMessage' => $api
                ];
            } else {
                return [
                    'success' => false,
                    'message' => __('Failed to send coin,  coin not found'),
                    'nodeMessage' => ''
                ];
            }
        } catch (\Exception $e) {
            storeException('external_transfer_using_coin_api ', $e->getMessage());
            return [
                'success' => false,
                'message' => __('Failed to send coin!'),
            ];
        }
    }

    // withdrawal process
    public function withdrawalProcess($request)
    {
        try {
            $google2faService = new User2FAService();
            $user = Auth::user();
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $request->wallet_id, 'wallets.user_id' => $user->id])
                ->select(
                    'wallets.*',
                    'coins.status as coin_status',
                    'coins.is_withdrawal',
                    'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal',
                    'coins.withdrawal_fees',
                    'coins.max_send_limit',
                    'coins.withdrawal_fees_type',
                    'coins.network'
                )
                ->first();
            if ($wallet) {
                if ($wallet->coin_type == COIN_USDT && $wallet->network == COIN_PAYMENT) {
                    $checkNetwork = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                    if (empty($checkNetwork)) {
                        return responseData(false, __('Selected network not found'), $wallet->coin_type);
                    }
                }
                $checkValidate = $this->checkWithdrawalValidation($request->address, $request->amount, $user, $wallet);
                if ($checkValidate['success'] ==  false) {
                    $response = [
                        'success' => false,
                        'message' => $checkValidate['message'],
                        'data' => ''
                    ];
                } else {
                    $data = [
                        'wallet_id' => $wallet->id,
                        'amount' => $request->amount,
                        'address' => $request->address,
                        'note' => $request->note ?? '',
                        'user' => $user,
                        'network_type' => '',
                        'memo' => $request->memo ? $request->memo : ''
                    ];

                    if ($wallet->coin_type == COIN_USDT && $wallet->network == COIN_PAYMENT) {
                        $data['network_type'] = $request->network_type;
                    }
                    if (isset($request->code) && !defined("IS_PUBLIC_API")) {
                        $request->merge(['code_type' => GOOGLE_AUTH]);
                        $response = checkTwoFactor("two_factor_withdraw", $request);
                        if (!$response["success"]) {
                            return $response;
                        }
                    }
                    dispatch(new Withdrawal($data))->onQueue('withdrawal');
                    if (checkCryptoAdminApproval($request->amount, $wallet->coin_id)) {
                        $message = __('Withdrawal process started successfully. Please wait for admin approval');
                    } else {
                        $message = __('Withdrawal process started successfully. We will notify you the result soon');
                    }
                    $response = [
                        'success' => true,
                        'message' => $message,
                        'data' => ''
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found'),
                    'data' => ''
                ];
            }
        } catch (\Exception $e) {
            storeException('withdrawalProcess ', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ''
            ];
        }

        return $response;
    }

    // kyc validation check
    public function kycValidationCheck($userId)
    {
        $response = [
            'success' => true,
            'message' => __('success ')
        ];
        if (settings('kyc_enable_for_withdrawal') == STATUS_ACTIVE) {
            if (settings('kyc_nid_enable_for_withdrawal') == STATUS_ACTIVE) {
                $checkNid = checkUserKyc($userId, KYC_NID_REQUIRED, __('withdrawal '));
                if ($checkNid['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkNid['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if (settings('kyc_passport_enable_for_withdrawal') ==  STATUS_ACTIVE) {
                $checkPass = checkUserKyc($userId, KYC_PASSPORT_REQUIRED, __('withdrawal '));
                if ($checkPass['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkPass['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
            if (settings('kyc_driving_enable_for_withdrawal') ==  STATUS_ACTIVE) {
                $checkDrive = checkUserKyc($userId, KYC_DRIVING_REQUIRED, __('withdrawal '));
                if ($checkDrive['success'] == false) {
                    $response = [
                        'success' => false,
                        'message' => $checkDrive['message']
                    ];
                    return $response;
                } else {
                    $response = [
                        'success' => true,
                        'message' => __('success ')
                    ];
                }
            }
        } else {
            $response = [
                'success' => true,
                'message' => __('success ')
            ];
        }

        return $response;
    }

    // send coin with bitgo
    public function sendCoinWithBitgo($transaction)
    {
        try {
            $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $transaction->coin_type])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $currency =  !empty($transaction->network_type) ? $transaction->network_type : $transaction->coin_type;
                $response = $this->sendBitgoCoin($currency, $coin->bitgo_wallet_id, $transaction->amount, $transaction->address, decryptId($coin->bitgo_wallet));
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Coin not found'),
                    'data' => ''
                ];
            }
        } catch (\Exception $e) {
            storeException('sendCoinWithBitgo', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ''
            ];
        }
        return $response;
    }

    // send bitgo coins
    public function sendBitgoCoin($coinType, $walletId, $amount, $address, $walletPassphrase)
    {
        try {
            $bitgoService = new BitgoWalletService();
            $bitgoResponse = $bitgoService->sendCoinsWithBitgo($coinType, $walletId, $amount, $address, $walletPassphrase);
            storeBotException('send coin api response', json_encode($bitgoResponse));

            if ($bitgoResponse['success'] == true) {
                $response = [
                    'success' => true,
                    'message' => __('Coin send successful'),
                    'data' => $bitgoResponse['data']['txid'],
                ];
            } else {
                storeBotException('Bitgo sendCoin', $bitgoResponse['message']);
                $response = [
                    'success' => false,
                    'message' => $bitgoResponse['message'],
                    'data' => ""
                ];
            }
        } catch (\Exception $e) {
            storeException('sendBitgoCoin', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => ""
            ];
        }
        return $response;
    }

    // send coin with erc20 api
    public function sendCoinWithERC20($transaction)
    {
        try {
            $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
                ->where(['coins.coin_type' => $transaction->coin_type])
                ->select('coins.*', 'coin_settings.*')
                ->first();
            if ($coin) {
                $coinApi = new ERC20TokenApi($coin);
                $requestData = [
                    "amount_value" => (float)$transaction->amount,
                    "from_address" => $coin->wallet_address,
                    "to_address" => $transaction->address,
                    "contracts" => decryptId($coin->wallet_key)
                ];
                $result = $coinApi->sendCustomToken($requestData);
                storeException('sendCoinWithERC20', json_encode($result));
                if ($result['success'] ==  true) {
                    $data['transaction_id'] = $result['data']->hash;
                    $data['used_gas'] = $result['data']->used_gas;
                    $response = [
                        'success' => true,
                        'message' => __('Coin sent successfully'),
                        'data' => $data
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => $result['message'] ?? __('Coin send failed'),
                        'data' => []
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Coin not found'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('sendCoinWithERC20', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => []
            ];
        }
        return $response;
    }

    // pre withdrawal process
    public function preWithdrawalProcess($request)
    {
        try {
            $user = Auth::user();
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $request->wallet_id, 'wallets.user_id' => $user->id])
                ->select(
                    'wallets.*',
                    'coins.status as coin_status',
                    'coins.is_withdrawal',
                    'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal',
                    'coins.withdrawal_fees',
                    'coins.max_send_limit',
                    'coins.withdrawal_fees_type',
                    'coins.network'
                )
                ->first();
            if ($wallet) {
                $amount = $request->amount ? $request->amount : 0;
                $address = $request->address ? $request->address : 'address';
                $fees = check_withdrawal_fees($amount, $wallet->withdrawal_fees, $wallet->withdrawal_fees_type);
                $response = [
                    'data' => [
                        'coin_type' => $wallet->coin_type,
                        'fees' => $fees,
                        'amount' => $amount,
                        'fees_percentage' => $wallet->withdrawal_fees,
                        'fees_type' => $wallet->withdrawal_fees_type,
                    ],
                    'success' => true,
                    'message' => __('Success')
                ];
                $walletAddress = $this->isInternalAddress($address);
                if ($walletAddress) {
                    $response = [
                        'data' => [
                            'coin_type' => $wallet->coin_type,
                            'fees' => 0,
                            'amount' => $amount,
                            'fees_percentage' => $wallet->withdrawal_fees,
                            'fees_type' => $wallet->withdrawal_fees_type,
                        ],
                        'success' => true,
                        'message' => __('Success')
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('pre withdrawalProcess ', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong'),
                'data' => []
            ];
        }

        return $response;
    }
}
