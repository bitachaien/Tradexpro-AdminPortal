<?php

namespace App\Http\Services;

use Stripe\Charge;
use Stripe\Stripe;
use App\Model\Bank;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\CurrencyList;
use App\Model\CurrencyDeposit;
use App\Http\Services\MailService;
use Illuminate\Support\Facades\DB;
use App\Model\CurrencyDepositHistory;
use App\Model\CurrencyDepositPaymentMethod;
use App\Http\Repositories\CurrencyDepositRepository;
use App\Traits\ResponseFormatTrait;

class CurrencyDepositService extends BaseService
{
    use ResponseFormatTrait;

    public $model = CurrencyDeposit::class;
    public $repository = CurrencyDepositRepository::class;
    public $walletService;
    public $paymentService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->walletService = new UserWalletService();
        $this->paymentService = new PaymentMethodService();
    }

    // send currency deposit process
    public function sendCurrencyDepositRequest($request, $user)
    {
        DB::beginTransaction();
        try {
            $message = __('Deposit request submitted successfully. Please wait for admin approval');
            $validation = $this->checkDepositValidation($request, $user);
            if ($validation['success'] == false) {
                return responseData(false, $validation['message']);
            }
            $validationData = $validation['data'];
            $requestData = $this->makeDepositData($validationData);

            if ($validationData['paymentMethod']->payment_method == WALLET_DEPOSIT) {
                $walletDeposit = $this->depositWithWallet($validationData);
                if ($walletDeposit['success'] == false) {
                    return responseData(false, $walletDeposit['message']);
                }

                $validationData['fromWallet']->decrement('balance', $validationData['amount']);
                $validationData['wallet']->increment('balance', $requestData['coin_amount']);
                $message = $validationData['wallet']->coin_type . ' ' . __('Deposited successfully.');
                $requestData['status'] = STATUS_SUCCESS;
                $requestData['transaction_id'] = $walletDeposit['data']['transaction_id'];
            } elseif ($validationData['paymentMethod']->payment_method == BANK_DEPOSIT) {

                $requestData['bank_receipt'] = uploadFile($request->bank_receipt, IMG_SLEEP_PATH, '');
                $message = __('Bank deposit request submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == STRIPE) {

                $stripeDeposit = $this->depositWithStripe($validationData);
                if ($stripeDeposit['success'] == false) {
                    return responseData(false, $stripeDeposit['message']);
                }
                $requestData['transaction_id'] = $stripeDeposit['data']['transaction_id'];
                $message = __('Deposit request with credit card submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == PAYPAL) {

                $requestData['transaction_id'] = $request->paypal_token;
                $message = __('Deposit request with paypal submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == PAYSTACK) {

                $requestData['transaction_id'] = $request->transaction_id;
                $message = __('Deposit request with Paystack is submitted successfully. Please wait for admin approval');
            } else {
                return responseData(false, __('Undefined payment method'));
            }
            $insert = $this->object->create($requestData);
            $response = responseData(true, $message, $this->object->getById($insert->id));
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('sendCurrencyDepositRequest', $e->getMessage());
            $response = responseData(false);
        }
        DB::commit();
        return $response;
    }

    // deposit with own wallet
    public function depositWithWallet($data)
    {
        $rateAmount = bcmulx($data['amount'], $data['rate'], 8);
        if ($data['amount'] > $data['fromWallet']->balance) {
            return responseData(false, __("You don't have enough balance to deposit"));
        }
        $returnData['rateAmount'] = $rateAmount;
        $returnData['transaction_id'] = uniqid() . date('') . time() . $data['user_id'];
        return responseData(true, __('Success'), $returnData);
    }

    // check deposit validation
    public function checkDepositValidation($request, $user)
    {
        try {
            $data['user'] = $user;
            $data['user_email'] = $user->email;
            $data['user_id'] = $user->id;
            $data['amount'] = $request->amount;
            if ($request->payment_method_id == 'pay_stack') {
                $CurrencyDepositPaymentMethod = CurrencyDepositPaymentMethod::where(['payment_method' => PAYSTACK])->first();
                if (empty($CurrencyDepositPaymentMethod)) {
                    return responseData(false, __('Payment method not found'));
                }
                $request->merge(['payment_method_id' => $CurrencyDepositPaymentMethod->id]);
            }
            $data['payment_method_id'] = $request->payment_method_id;
            $data['currency'] = $request->currency ?? '';
            $data['stripeToken'] = $request->stripe_token ?? '';
            $data['wallet'] = $this->walletService->whereFirst(['user_id' => $user->id, 'id' => $request->wallet_id]);
            if (empty($data['wallet'])) {
                return responseData(false, __('Wallet not found'));
            }
            $data['paymentMethod'] = $this->paymentService->whereFirst(['id' => $request->payment_method_id, 'status' => STATUS_SUCCESS]);
            if (empty($data['paymentMethod'])) {
                return responseData(false, __('Payment method not found'));
            }
            if (!empty($data['currency'])) {
                $data['checkCurrency'] = CurrencyList::where(['code' => $request->currency, 'status' => STATUS_SUCCESS])->first();
                if (empty($data['checkCurrency'])) {
                    return responseData(false, __('Currency not found'));
                }
            }
            if ($data['paymentMethod']->payment_method == WALLET_DEPOSIT) {
                $data['fromWallet'] = $this->walletService->whereFirst(['user_id' => $user->id, 'id' => $request->from_wallet_id]);
                if (empty($data['fromWallet'])) {
                    return responseData(false, __('Selected wallet not found'));
                }
                if ($data['fromWallet']->coin_type == $data['wallet']->coin_type) {
                    return responseData(false, __('From wallet and to wallet should not be same'));
                }
            }
            if ($data['paymentMethod']->payment_method == BANK_DEPOSIT) {
                if (empty($request->rate_request)) {
                    $data['bank_id'] = $request->bank_id;
                    $data['bank'] = Bank::where(['id' => $data['bank_id'], 'status' => STATUS_SUCCESS])->first();
                    if (empty($data['bank'])) {
                        return responseData(false, __('Invalid bank'));
                    }
                }
            }
            $data['rate'] = $this->getDepositRate($request, $data['wallet'], $data['paymentMethod'], $user);
            // if ($data['rate'] == 0) {
            //     return responseData(false,__('Rate calculation failed'));
            // }
            $data['fees_type'] = isset(settings()['fiat_deposit_fees_type']) ? settings()['fiat_deposit_fees_type'] : 1;
            $data['calculated_amount'] = bcmulx($data['rate'], $request->amount, 8);
            $data['fees'] = getCalculatedFiatDepositFees($data['calculated_amount']);
            $data['net_amount'] = bcsubx($data['calculated_amount'], $data['fees'], 8);
            $data['coin_type'] = $data['wallet']->coin_type;

            $response = responseData(true, __('Success'), $data);
        } catch (\Exception $e) {
            storeException('checkDepositValidation', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    // get deposit rate
    public function getDepositRate($request, $wallet, $paymentMethod, $user)
    {
        if ($paymentMethod->payment_method == WALLET_DEPOSIT) {
            $fromWallet = $this->walletService->whereFirst(['user_id' => $user->id, 'id' => $request->from_wallet_id]);
            $coinRate = convert_currency(1, $wallet->coin_type, $fromWallet->coin_type);
        } else {
            $coinRate = convert_currency(1, $wallet->coin_type, $request->currency, $request->currency);
        }
        return $coinRate;
    }

    // deposit data
    public function makeDepositData($data)
    {
        return [
            'unique_code' => uniqid() . date('') . time() . $data['user_id'],
            'user_id' => $data['user_id'],
            'wallet_id' => $data['wallet']->id,
            'payment_method_id' => $data['payment_method_id'],
            'currency' => isset($data['fromWallet']) ? $data['fromWallet']->coin_type : $data['currency'],
            'from_wallet_id' => isset($data['fromWallet']) ? $data['fromWallet']->id : NULL,
            'currency_amount' => $data['amount'],
            'fees_type' => $data['fees_type'],
            'fees' => $data['fees'],
            'coin_amount' => $data['net_amount'],
            'rate' => $data['rate'],
            'bank_id' => $data['paymentMethod']->payment_method == BANK_DEPOSIT ? $data['bank_id'] : NULL,
        ];
    }

    // get currency deposit rate
    public function getCurrencyDepositRate($request, $user)
    {
        $request->merge(['rate_request' => STATUS_SUCCESS]);
        $validation = $this->checkDepositValidation($request, $user);
        if ($validation['success'] == false) {
            return responseData(false, $validation['message']);
        }
        $data['rate'] = $validation['data']['rate'];
        $data['calculated_amount'] = $validation['data']['calculated_amount'];
        $data['fees'] = $validation['data']['fees'];
        $data['net_amount'] = $validation['data']['net_amount'];
        $data['coin_type'] = $validation['data']['coin_type'];

        return responseData(true, $validation['message'], $data);
    }

    // currency deposit accept process
    public function currencyDepositAcceptProcess($id)
    {
        try {
            $deposit = $this->object->whereFirst(['unique_code' => decrypt($id), 'status' => STATUS_PENDING]);
            if ($deposit) {
                $wallet =  $this->walletService->whereFirst(['id' => $deposit->wallet_id]);
                $wallet->increment('balance', $deposit['coin_amount']);
                $deposit->status = STATUS_ACCEPTED;
                $deposit->save();

                $response = responseData(true, __('Deposit accepted successfully'));
            } else {
                $response = responseData(false);
            }
        } catch (\Exception $e) {
            storeException('currencyDepositAcceptProcess', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }
    // currency deposit reject process
    public function currencyDepositRejectProcess($request)
    {
        try {
            $deposit = $this->object->whereFirst(['unique_code' => decrypt($request->id), 'status' => STATUS_PENDING]);

            if ($deposit) {

                $deposit->status = STATUS_REJECTED;
                $deposit->save();

                if (isset($deposit->user) && !empty($deposit->user->email)) {
                    $userName = $deposit->user->first_name . ' ' . $deposit->user->last_name;
                    $data['user_name'] = $userName;
                    $data['rejected_note'] = $request->reject_note ?? __('Your currency deposit is rejected');

                    $mailService = new MailService();
                    $userEmail = $deposit->user->email;
                    $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                    $subject = __('Currency Deposit rejected Reason | :companyName', ['companyName' => $companyName]);
                    $template = emailTemplateName('currency_deposit_rejected');
                    $mailService->send($template, $data, $userEmail, $userName, $subject);
                }

                $response = responseData(true, __('Deposit rejected successfully'));
            } else {
                $response = responseData(false);
            }
        } catch (\Exception $e) {
            storeException('currencyDepositAcceptProcess', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    public function getPendingDepositList()
    {
        try {
            return $this->object->getPendingDepositList();
        } catch (\Exception $e) {
            storeException('getPendingDepositList', $e->getMessage());
        }
    }

    public function getDepositHistory($userId, $request)
    {
        return $this->object->getDepositHistory($userId, $request->per_page, $request->search);
    }

    // deposit with stripe
    public function depositWithStripe($data)
    {
        try {
            $stripe_secret = settings('STRIPE_SECRET');
            Stripe::setApiKey($stripe_secret);
            $charge = Charge::create([
                "amount" => (int)$data['amount'] * 100,
                "currency" => "usd",
                "source" => $data['stripeToken'],
                "description" => "Payment from " . $data['user_email'] . ' for ' . $data['amount'] . ' usd'
            ]);
            if (isset($charge) && $charge['status'] == 'succeeded') {
                $returnData['transaction_id'] = $charge['id'];
                $response = $this->responseData(true, __('Payment success'), $returnData);
            } else {
                $response = $this->responseData(false, __('Payment failed'));
            }
        } catch (\Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            $response = $this->responseData(false, $e->getMessage());
        }
        return $response;
    }

    public function sendWalletCurrencyDepositRequest($request, $user)
    {
        DB::beginTransaction();
        try {
            $message = __('Deposit request submitted successfully. Please wait for admin approval');
            $validation = $this->checkWalletDepositValidation($request, $user);
            if ($validation['success'] == false) {
                return responseData(false, $validation['message']);
            }
            $validationData = $validation['data'];
            $requestData = $this->makeWalletDepositData($validationData);

            if ($validationData['paymentMethod']->payment_method == BANK_DEPOSIT) {
                $requestData['bank_recipt'] = uploadFile($request->bank_receipt, IMG_SLEEP_PATH, '');
                $message = __('Bank deposit request submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == STRIPE) {
                $stripeDeposit = $this->depositWithStripe($validationData);
                if ($stripeDeposit['success'] == false) {
                    return responseData(false, $stripeDeposit['message']);
                }
                $requestData['transaction_id'] = $stripeDeposit['data']['transaction_id'];
                $message = __('Deposit request with credit card submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == PAYPAL) {
                $requestData['transaction_id'] = $request->paypal_token;
                $message = __('Deposit request with paypal submitted successfully. Please wait for admin approval');
            } elseif ($validationData['paymentMethod']->payment_method == PAYSTACK) {
                $requestData['transaction_id'] = $request->transaction_id;
                $message = __('Deposit request with Paystack is submitted successfully. Please wait for admin approval');
            } else {
                return responseData(false, __('Undefined payment method'));
            }
            if ($insert = CurrencyDepositHistory::create($requestData)) {
                $response = responseData(true, $message, $insert);
            } else {
                $response = responseData(false, $message);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('sendCurrencyDepositRequest', $e->getMessage());
            $response = responseData(false);
        }
        DB::commit();
        return $response;
    }


    public function checkWalletDepositValidation($request, $user)
    {
        try {
            $data['user'] = $user;
            $data['user_email'] = $user->email;
            $data['user_id'] = $user->id;
            $data['amount'] = $request->amount;
            $payment_method = CurrencyDepositPaymentMethod::find($request->payment_method_id);
            if ($payment_method) {
                $data['payment_type'] = $payment_method->payment_method;
                $data['paymentMethod'] = $payment_method;
            } else {
                return responseData(false, __('Payment method not found'));
            }
            $data['payment_method_id'] = $request->payment_method_id;
            $data['currency'] = $request->currency ?? '';
            $data['stripeToken'] = $request->stripe_token ?? '';
            $wallet = Wallet::where(['user_id' => $user->id, 'coin_type' => $request->currency])->first();
            if (! $wallet) {
                return responseData(false, __('Wallet not found'));
            }
            $data['wallet_id'] = $wallet->id;
            $coin = Coin::where('coin_type', $request->currency)->first();
            if (! $coin) {
                return responseData(false, __('Coin not found'));
            }
            $data['coin_id'] = $coin->id;
            $data['coin_type'] = $coin->coin_type;
            if (!empty($data['currency'])) {
                $data['checkCurrency'] = CurrencyList::where(['code' => $request->currency, 'status' => STATUS_SUCCESS])->first();
                if (empty($data['checkCurrency'])) {
                    return responseData(false, __('Currency not found'));
                }
            }
            if ($payment_method->payment_method == BANK_DEPOSIT) {
                if (empty($request->rate_request)) {
                    $data['bank_id'] = $request->bank_id;
                    $data['bank'] = Bank::where(['id' => $data['bank_id'], 'status' => STATUS_SUCCESS])->first();
                    if (empty($data['bank'])) {
                        return responseData(false, __('Invalid bank'));
                    }
                }
            }
            $response = responseData(true, __('Success'), $data);
        } catch (\Exception $e) {
            storeException('checkDepositValidation', $e->getMessage());
            $response = responseData(false);
        }
        return $response;
    }

    public function makeWalletDepositData($data)
    {
        return [
            "user_id" =>  $data['user_id'],
            "payment_id" => $data['payment_method_id'],
            "payment_type" => $data['payment_type'] ?? 0,
            "wallet_id" => $data['wallet_id'],
            "coin_id" => $data['coin_id'],
            "coin_type" => $data['coin_type'],
            "bank_id" => $data['bank_id'] ?? NULL,
            "amount" => $data['amount'],
        ];
    }

    public function currencyWalletDepositHistory($request)
    {
        $limit = $request->limit ?? 20;
        $search = $request->search ?? "";
        $items = CurrencyDepositHistory::with([
            'payment_method:id,title,payment_method',
            'bank',
        ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->where('amount', "<=", $search)
                    ->orWhere('coin_type', "LIKE", "%" . $search . "%")
                    ->orWhere('transaction_id', "LIKE", "%" . $search . "%")
                    ->orWhereHas('bank', function ($q) use ($search) {
                        $q->where('bank_name', "LIKE", "%" . $search . "%");
                    })
                    ->orWhereHas('payment_method', function ($q) use ($search) {
                        $q->where('title', "LIKE", "%" . $search . "%");
                    });
            })
            ->where('user_id', getUserId())->orderBy('id', 'desc')->paginate($limit);

        $items->map(function ($item) {
            if ($item->payment_type == BANK_DEPOSIT) {
                $item->bank_recipt = empty($item->bank_recipt) ? null : asset(IMG_SLEEP_PATH . $item->bank_recipt);
            }
            $item->payment_title =  ($item->payment_type == BANK_DEPOSIT) ? $item?->bank?->bank_name : $item?->payment_method?->title;
            $item->payment_type = currencyDepositPaymentMethod($item->payment_type);
            if (empty($item->transaction_id)) $item->transaction_id = "N/A";
            $item->status = deposit_status_text($item->status);
        });

        return responseData(true, __("Deposit History get successfully"), $items);
    }
}
