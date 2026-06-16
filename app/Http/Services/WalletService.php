<?php

namespace App\Http\Services;

use App\Exceptions\InvalidRequestException;
use App\Http\Repositories\DashboardRepository;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\FutureWallet;
use App\Model\WalletNetwork;
use App\Model\WithdrawHistory;
use App\Model\DepositeTransaction;
use App\Model\AdminSendCoinHistory;
use App\Model\WalletAddressHistory;
use Illuminate\Support\Facades\Auth;
use App\Model\AdminWalletDeductHistory;
use App\Http\Repositories\WalletRepository;
use App\Model\WalletSwapHistory;
use App\Traits\NumberFormatTrait;
use App\Traits\ResponseFormatTrait;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletService
{
    use NumberFormatTrait, ResponseFormatTrait;

    public $repository;
    public $bitgoService;

    public function __construct()
    {
        $this->repository = new WalletRepository();
        $this->bitgoService = new BitgoWalletService();
    }

    // user wallet list
    public function userWalletList($userId, $request): array
    {
        $list = isset($request->type) && $request->type == 'usd'
            ? $this->repository->getMyWalletListWithOnOrderWithTotal($userId, $request->per_page, $request->search)
            : $this->repository->getMyWalletListWithOnOrderWithTotalWithoutUSD($userId, $request->per_page, $request->search);

        $data = [
            'currency' => "USD",
            'wallets' => $list['wallets'],
        ];
        return $this->responseData(true, __('Data get'), $data);
    }

    //get user wallet list only
    public function getUserWalletList($userId)
    {
        return $this->repository->getUserWalletList($userId);
    }

    // user wallet deposit address
    public function userWalletDeposit($userId, $walletId)
    {
        try {
            $response = $this->repository->walletDeposit($userId, $walletId);
        } catch (\Exception $e) {
            storeException('userWalletList', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $response;
    }

    // user wallet withdrawal
    public function userWalletWithdrawal($userId, $walletId)
    {
        try {
            $response = $this->repository->walletWithdrawal($userId, $walletId);
        } catch (\Exception $e) {
            storeException('userWalletWithdrawal', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $response;
    }

    public function get_wallet_rate($request)
    {
        return $this->repository->get_wallet_rate($request);
    }

    public function coinSwap($from_wallet, $to_wallet, $converted_amount, $requested_amount, $rate)
    {
        return $this->repository->coinSwap($from_wallet, $to_wallet, $converted_amount, $requested_amount, $rate);
    }

    // bitgo wallet deposit
    public function bitgoWalletCoinDeposit($coinType, $walletId, $txId)
    {
        try {
            $bitgoService = new BitgoWalletService();
            $checkHash = DepositeTransaction::where(['transaction_id' => $txId])->first();
            if (isset($checkHash)) {
                storeBotException('bitgoWalletCoinDeposit hash already in db ', $txId);
            } else {
                $getTransaction = $this->getTransaction($coinType, $walletId, $txId);
                if ($getTransaction['success'] == true) {
                    $transactionData = $getTransaction['data'];
                    if ($transactionData['type'] == 'receive' && $transactionData['state'] == 'confirmed') {
                        $coinVal = $bitgoService->getDepositDivisibilityValues($transactionData['coin']);
                        $amount = bcdivx($transactionData['value'], $coinVal, 8);

                        $data = [
                            'coin_type' => $transactionData['coin'],
                            'txId' => $transactionData['txid'],
                            'confirmations' => $transactionData['confirmations'],
                            'amount' => $amount
                        ];

                        if (isset($transactionData['entries'][0])) {
                            foreach ($transactionData['entries'] as $entry) {
                                if (isset($entry['wallet']) && ($entry['wallet'] == $transactionData['wallet'])) {
                                    $data['address'] = $entry['address'];
                                    storeBotException('entry address', $data['address']);
                                }
                            }
                        }

                        if (isset($data['address'])) {
                            $this->checkAddressAndDeposit($data);
                        }
                    } else {
                        storeException('bitgoWalletCoinDeposit type', 'the transaction type is not receive');
                    }
                } else {
                    storeException('bitgoWalletCoinDeposit failed', $getTransaction['message']);
                }
            }
        } catch (\Exception $e) {
            storeException('bitgoWalletCoinDeposit', $e->getMessage());
        }
    }

    // get transaction
    public function getTransaction($coinType, $walletId, $txId)
    {
        try {
            $bitgoResponse = $this->bitgoService->transferBitgoData($coinType, $walletId, $txId);
            storeBotException('getTransaction response ', json_encode($bitgoResponse));
            if ($bitgoResponse['success']) {

                $response = [
                    'success' => true,
                    'message' => __('Data get successfully'),
                    'data' => $bitgoResponse['data']
                ];
            } else {
                storeException('getTransaction', $bitgoResponse['message']);
                $response = [
                    'success' => false,
                    'message' => $bitgoResponse['message'],
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeException('bitgoWalletWebhook getTransaction', $e->getMessage());
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
        return $response;
    }
    // check deposit address
    public function checkAddressAndDeposit($data)
    {
        try {
            storeBotException('checkAddressAndDeposit', json_encode($data));
            $checkAddress = WalletAddressHistory::where(['address' => $data['address'], 'coin_type' => $data['coin_type']])->first();
            if ($checkAddress) {
                $wallet = Wallet::find($checkAddress->wallet_id);
                if ($wallet) {
                    storeBotException('checkAddressAndDeposit wallet ', json_encode($wallet));
                    $deposit = DepositeTransaction::create($this->depositData($data, $wallet));
                    storeException('checkAddressAndDeposit created ', json_encode($deposit));
                    storeException('checkAddressAndDeposit wallet balance before ', $wallet->balance);
                    $wallet->increment('balance', $data['amount']);
                    storeException('checkAddressAndDeposit wallet balance increment ', $wallet->balance);
                    storeException('checkAddressAndDeposit', ' wallet deposit successful');
                    $response = responseData(false, __('Wallet deposited successfully'));
                } else {
                    storeBotException('checkAddressAndDeposit', ' wallet not found');
                    $response = responseData(false, __('wallet not found'));
                }
            } else {
                storeBotException('checkAddressAndDeposit', $data['address'] . ' this address not found in db ');
                $response = responseData(false, __('This address not found in db the address is ') . $data['address']);
            }
        } catch (\Exception $e) {
            storeException('checkAddressAndDeposit', $e->getMessage());
            $response = responseData(false, $e->getMessage());
        }
        return $response;
    }
    // deposit data
    public function depositData($data, $wallet)
    {
        return [
            'address' => $data['address'],
            'from_address' => isset($data['from_address']) ? $data['from_address'] : "",
            'receiver_wallet_id' => $wallet->id,
            'address_type' => ADDRESS_TYPE_EXTERNAL,
            'coin_type' => $wallet->coin_type,
            'amount' => $data['amount'],
            'transaction_id' => $data['txId'],
            'status' => STATUS_SUCCESS,
            'confirmations' => $data['confirmations']
        ];
    }

    // send coin balance to user
    public function sendCoinBalanceToUser($request)
    {
        try {
            if (isset($request->wallet_id[0])) {
                $wallets = $request->wallet_id;
                $counts = sizeof($request->wallet_id);
                for ($i = 0; $i < $counts; $i++) {
                    $wallet = Wallet::find($wallets[$i]);
                    if (isset($wallet)) {
                        AdminSendCoinHistory::create($this->balanceSendData($wallet, $request->amount, Auth::id()));
                        $wallet->increment('balance', $request->amount);
                    }
                }
                $response = responseData(true, __('Coin sent successful'));
            } else {
                $response = responseData(false, __('Must select at least one wallet'));
            }
        } catch (\Exception $e) {
            storeException('sendCoinBalanceToUser', $e->getMessage());
            $response = responseData(false, __('Something went wrong'));
        }
        return $response;
    }

    // make wallet send history data
    public function balanceSendData($wallet, $amount, $authId)
    {
        return [
            'user_id' => $wallet->user_id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'updated_by' => $authId
        ];
    }

    // generate address
    public function getWalletNetworkAddress($request, $userId)
    {
        try {
            $wallet = Wallet::where(['id' => $request->wallet_id, 'user_id' => $userId])->first();
            if ($wallet) {
                if ($wallet->coin_type == 'USDT') {
                    $networkAddress = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                    if (empty($networkAddress)) {
                        $networkAddress = WalletNetwork::firstOrCreate(['wallet_id' => $wallet->id, 'network_type' => $request->network_type], ['coin_id' => $wallet->coin_id]);
                    }
                    if (empty($networkAddress->address)) {
                        $address = get_coin_address($networkAddress->network_type, $wallet->coin->network);
                        if (!empty($address['address'])) {
                            $networkAddress->update(['address' => $address['address']]);
                            $networkAddress = WalletNetwork::where(['wallet_id' => $wallet->id, 'network_type' => $request->network_type])->first();
                        }
                    }
                    if (empty($networkAddress->address)) {
                        $response = responseData(false, __('Address generate failed'), $networkAddress);
                    } else {
                        $response = responseData(true, __('Address generated successfully'), $networkAddress);
                    }
                } else {
                    $response = responseData(false, __('No need to create address with this coin'));
                }
            } else {
                $response = responseData(false, __('Wallet not found'));
            }
        } catch (\Exception $e) {
            storeException('getWalletNetworkAddress', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    public function adminSendBalanceDelete($id)
    {
        try {
            $sendCoinHistoryDetails = AdminSendCoinHistory::find($id);

            $depositBalance = DepositeTransaction::where('receiver_wallet_id', $sendCoinHistoryDetails->wallet_id)->get()->sum('amount');
            $userWallet = Wallet::find($sendCoinHistoryDetails->wallet_id);

            $userCurrentBalance = $userWallet->balance - $depositBalance;

            if ($userCurrentBalance > $sendCoinHistoryDetails->amount) {

                $userWallet->decrement('balance', $sendCoinHistoryDetails->amount);
            } else {
                if ($userCurrentBalance > 0) {
                    $userWallet->decrement('balance', $userCurrentBalance);
                }
            }
            $sendCoinHistoryDetails->delete();

            $response = ['success' => true, 'message' => __('Send Coin transaction deleted successfully!')];
            return $response;
        } catch (\Exception $e) {
            storeException('adminSendBalanceDelete', $e->getMessage());
            $response = ['success' => false, 'message' => __('Send Coin transaction is not deleted!')];
        }
        return $response;
    }

    public function deductWalletBalanceSave($request)
    {
        $wallet_details = Wallet::find(decrypt($request->wallet_id));
        if (isset($wallet_details)) {
            $new_balance = $wallet_details->balance - $request->deduct_amount;
            if ($new_balance > 0) {
                $deduct_wallet_balance_history = new AdminWalletDeductHistory();
                $deduct_wallet_balance_history->user_id = $wallet_details->user_id;
                $deduct_wallet_balance_history->wallet_id = $wallet_details->id;
                $deduct_wallet_balance_history->updated_by = auth()->user()->id;
                $deduct_wallet_balance_history->old_balance = $wallet_details->balance;
                $deduct_wallet_balance_history->deduct_amount = $request->deduct_amount;
                $deduct_wallet_balance_history->new_balance = $new_balance;
                $deduct_wallet_balance_history->reason = $request->reason;
                $deduct_wallet_balance_history->save();

                $wallet_details->decrement('balance', $request->deduct_amount);

                $response = ['success' => true, 'message' => __('Deduct Wallet Balance Successfully!')];
            } else {
                $response = ['success' => false, 'message' => __('This wallet has not enough balance to deduct this amount!')];
            }
        } else {
            $response = ['success' => false, 'message' => __('Wallet Not found!')];
        }

        return $response;
    }

    public function getWalletBalanceDetails($request)
    {

        $coin = $request->coin_type ?? "BTC";
        $total = 0;
        $data = [];
        $setting = settings(['p2p_module', 'wallet_overview_selected_coins', 'wallet_overview_banner']);
        $string_coins = $setting["wallet_overview_selected_coins"] ?? "[]";
        $coin_array = json_decode($string_coins);
        $p2p_enable = ($setting['p2p_module'] ?? 0) ? 1 : 0;
        if (!(json_last_error() === JSON_ERROR_NONE))
            $coin_array = [];
        if (!empty($coin_array))
            $coin = $request->coin_type ?? $coin_array[0];
        else {
            if ($coin_data = Coin::first())
                $coin = $coin_data->coin_type;
            else
                $coin = "BTC";
        }

        $spot_wallet = Wallet::where(["user_id" => getUserId(), "coin_type" => $coin])->first();
        $future_wallet = FutureWallet::where(["user_id" => getUserId(), "coin_type" => $coin])->first();
        $p2p_wallet = null;

        if ($p2p_enable && class_exists(\Modules\P2P\Entities\P2PWallet::class))
            $p2p_wallet = \Modules\P2P\Entities\P2PWallet::where(["user_id" => getUserId(), "coin_type" => $coin])->first();

        if ($spot_wallet) {
            $data['spot_wallet'] = $this->trimNum($spot_wallet->balance ?? 0);
            $data['spot_wallet_usd'] = userCurrencyConvert($data['spot_wallet'], $coin);
            $total += $data['spot_wallet'];
        }

        if ($future_wallet) {
            $data['future_wallet'] = $this->trimNum($future_wallet->balance ?? 0);
            $data['future_wallet_usd'] = userCurrencyConvert($data['future_wallet'], $coin);
            $total += $data['future_wallet'];
        }

        if ($p2p_wallet) {
            $data['p2p_wallet'] = $this->trimNum($p2p_wallet->balance ?? 0);
            $data['p2p_wallet_usd'] = userCurrencyConvert($data['p2p_wallet'], $coin);
            $total += $data['p2p_wallet'];
        }
        $data['currency'] = auth()->user()->currency ?? "USD";
        $data['total'] = $this->trimNum($total);
        $data['total_usd'] = userCurrencyConvert($total, $coin);
        $data['coins'] = $coin_array;
        $data['selected_coin'] = $coin;
        $data['banner'] = isset($setting['wallet_overview_banner']) ? asset(IMG_PATH . $setting['wallet_overview_banner']) : null;

        $data['withdraw'] = WithdrawHistory::where(['user_id' => getUserId(), "coin_type" => $coin, 'status' => STATUS_ACCEPTED])->latest()->limit(2)->get(["coin_type", "amount", "status", "created_at"]);
        if ($wallet = Wallet::where(['user_id' => getUserId(), "coin_type" => $coin])->first()) {
            $data['deposit'] = DepositeTransaction::where(['receiver_wallet_id' => $wallet->id, "coin_type" => $coin, 'status' => STATUS_ACCEPTED])->latest()->limit(2)->get(["coin_type", "amount", "status", "created_at"]);
        }

        return responseData(true, __("Wallet overview get successfully"), $data);
    }

    public function userWalletTotalValue(int $user_id)
    {
        $total = 0;
        $dashboardRepo = new DashboardRepository();
        $wallets = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where([
                'wallets.user_id' => $user_id,
                'wallets.type' => PERSONAL_WALLET,
                'coins.status' => STATUS_ACTIVE
            ])
            ->select("wallets.coin_id", "wallets.balance", "wallets.coin_type", "wallets.user_id", "wallets.type", "coins.status")
            ->get();

        if (isset($wallets[0])) {
            $wallets->map(function ($wallet) use ($dashboardRepo, &$total, $user_id) {
                $wallet->on_order = $dashboardRepo->getOnOrderBalance($wallet->coin_id, $user_id);
                $wallet->available_balance = $wallet->balance;
                $wallet->total = bcaddx($wallet->on_order, $wallet->available_balance, 8);

                $wallet->total_balance_usd = get_coin_usd_value($wallet->total, $wallet->coin_type);
                $total = $total + $wallet->total_balance_usd;
            });
        }
        $total = $this->trimNum((is_numeric($total) && ($total > 0)) ? $total : 0);

        $result = [
            'total' => $total,
            'currency' => 'USD'
        ];

        return $this->responseData(true, __("Total balance value found"), $result);
    }

    public function walletInfo(int $userID, Coin|int|string $coin)
    {
        return $this->repository->walletInfo($userID, $coin);
    }


    public function bulkWalletGenerate($id, $type)
    {
        if ($type == WALLET_GENERATE_BY_COIN) {
            $coin = Coin::find($id);
            $users = User::where(['status' => STATUS_ACTIVE, 'super_admin' => STATUS_INACTIVE])->get();

            foreach ($users as $user) {
                DB::beginTransaction();
                try {
                    $this->walletInfo($user->id, $coin);
                    DB::commit();
                } catch (\Exception $e) {
                    storeLog(processExceptionMsg($e));
                    DB::rollBack();
                }
            }
        } else if ($type == WALLET_GENERATE_BY_USER) {
            $user = User::find($id);
            $coins = Coin::where('status', STATUS_ACTIVE)->get();

            foreach ($coins as $coin) {
                DB::beginTransaction();
                try {
                    $this->walletInfo($user->id, $coin);
                    DB::commit();
                } catch (\Exception $e) {
                    storeLog(processExceptionMsg($e));
                    DB::rollBack();
                }
            }
        }
        return responseData(true, __('Wallet generated successfully'));
    }

    public function walletHistoryApp(Request $request, int $userId): array
    {
        $limit = $request->per_page ?? 5;
        $order_data = [
            'column_name' => $request->column_name ?? 'created_at',
            'order_by' => $request->order_by ?? 'DESC',
        ];
        $data = [];

        if (!in_array($request->type, ['deposit', 'withdraw'])) {
            throw new InvalidRequestException(__('Invalid request'));
        }
        $data['type'] = $request->type;
        $data['sub_menu'] = $request->type;

        $processObj = [
            'deposit' => [
                'title' => __('Deposit History'),
                'method' => 'depositTransactionHistories',
                'progress_status_setting' => 'progress_status_for_deposit',
                'progress_status_type' => PROGRESS_STATUS_TYPE_DEPOSIT,
            ],
            'withdraw' => [
                'title' => __('Withdrawal History'),
                'method' => 'withdrawTransactionHistories',
                'progress_status_setting' => 'progress_status_for_withdrawal',
                'progress_status_type' => PROGRESS_STATUS_TYPE_WITHDRAWN,
            ],
        ];

        $processData = $processObj[$request->type];

        $data['title'] = $processData['title'];

        $transService = new TransService();
        $data['histories'] = $transService->{$processData['method']}($userId, $request->status, $request->search, $order_data)->paginate($limit);

        $data['progress_status_for_deposit'] = allsetting('progress_status_for_deposit');
        $data['progress_status_for_withdrawal'] = allsetting('progress_status_for_withdrawal');

        if (allsetting($processData['progress_status_setting'])) {
            $progressService = new ProgressStatusService();
            $data['progress_status_list'] = $progressService->getProgressStatusActiveListBytype($processData['progress_status_type'])['data'];
        }
        $data['status'] = deposit_status();

        return responseData(true, $data['title'], $data);
    }

    public function coinSwapHistoryApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data = [
            'column_name' => $request->column_name ?? 'created_at',
            'order_by' => $request->order_by ?? 'DESC',
        ];
        $data['title'] = __('Coin swap history');
        $data['sub_menu'] = 'swap_history';
        $data['list'] = WalletSwapHistory::where(['user_id' => auth()->id()])
            ->when(isset($request->search), function ($query) use ($request) {
                $query->where('requested_amount', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('converted_amount', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('from_coin_type', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('to_coin_type', 'LIKE', '%' . $request->search . '%');
            })
            ->orderBy($order_data['column_name'], $order_data['order_by'])
            ->paginate($limit);

        foreach ($data['list'] as &$item) {
            $item->fromWallet = $item->fromWallet->name;
            $item->toWallet = $item->toWallet->name;
        }
        return responseData(true, $data['title'], $data);
    }
}
