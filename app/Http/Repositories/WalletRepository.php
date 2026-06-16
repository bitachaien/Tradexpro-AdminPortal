<?php

namespace App\Http\Repositories;

use App\Http\Services\BitCoinApiService;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\Logger;
use App\Http\Services\MyCommonService;
use App\Jobs\ConvertCoin;
use App\Model\Coin;
use App\Model\DepositeTransaction;
use App\Model\TempWithdraw;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletNetwork;
use App\Model\WithdrawHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class WalletRepository
{
    public function __construct() {}
    // user available balance
    public function availableBalance($user_id)
    {
        $balance = getUserBalance($user_id);
        $data['available_coin'] = number_format($balance['available_coin'], 8);
        $data['available_usd'] = number_format($balance['available_used'], 8);
        $data['coin_name'] = settings('coin_name');

        return $data;
    }
    // wallet withdrawal
    public function walletWithdrawal($user_id, $wallet_id)
    {
        try {
            $resData = [];
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $wallet_id, 'wallets.user_id' => $user_id])
                ->select(
                    'wallets.*',
                    'coins.status as coin_status',
                    'coins.is_withdrawal',
                    'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal',
                    'coins.withdrawal_fees',
                    'coins.coin_icon',
                    'coins.withdrawal_fees_type',
                    'coins.network'
                )
                ->first();
            $wallet->network_name = api_settings_new($wallet->network);
            if (!empty($wallet)) {
                if ($wallet->coin_type == COIN_USDT && $wallet->network == COIN_PAYMENT) {
                    $resData = $this->depositNetwotkAddress($wallet)['data'];
                }
                $wallet->coin_icon = empty($wallet->coin_icon) ? '' : show_image_path($wallet->coin_icon, 'coin/');

                if ($wallet->is_withdrawal == STATUS_ACTIVE) {
                    $response = [
                        'success' => true,
                        'wallet' => $wallet,
                        'message' => __('Wallet found'),
                        'data' => $resData
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => __('Withdrawal is currently disable')
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found')
                ];
            }
        } catch (\Exception $e) {
            storeException('walletWithdrawal ', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }
        return $response;
    }
    // user wallet deposit
    public function walletDeposit($user_id, $wallet_id)
    {
        try {
            $data['wallet_id'] = $wallet_id;
            $resData = [];
            $address['address'] = "";
            $address['memo'] = "";
            $data['wallet'] = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->leftjoin('coin_settings', 'coins.id', '=', 'coin_settings.coin_id')
                ->where(['wallets.id' => $wallet_id, 'wallets.user_id' => $user_id, 'coins.status' => STATUS_ACTIVE])
                ->select('wallets.*', 'coins.coin_icon', 'coins.network', 'coins.is_deposit', 'coin_settings.contract_address')
                ->first();

            //checking if co-wallet
            if (co_wallet_feature_active() && empty($data['wallet'])) {
                $data['co_wallet'] = Wallet::select('wallets.*')
                    ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
                    ->where(['wallets.id' => $data['wallet_id'], 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => $user_id])
                    ->first();
            }
            if (empty($data['wallet'])) {
                $response = [
                    'success' => false,
                    'message' => __('Wallet not found')
                ];
                return $response;
            }
            if ($data['wallet']->is_deposit != STATUS_ACTIVE) {
                $response = [
                    'success' => false,
                    'message' => __('Deposit is disable right now')
                ];
                return $response;
            }

            if ($data['wallet']->coin_type == 'USDT' && $data['wallet']->network == COIN_PAYMENT) {
                $resData = $this->depositNetwotkAddress($data['wallet'])['data'];
            } else {
                $exists = WalletAddressHistory::where('wallet_id', $wallet_id)->orderBy('created_at', 'desc')->first();
                if (isset($exists) && (!empty($exists->address))) {
                    $address['address'] = $exists->address;
                    $address['memo'] = $exists->memo;
                } else {
                    $address = get_coin_address($data['wallet']->coin_type, $data['wallet']->network);
                    if (!empty($address['address'])) {
                        $history = new \App\Http\Services\wallet();
                        $history->AddWalletAddressHistory($data['wallet']->id, $address['address'], $data['wallet']->coin_type, $address['wallet_key'], $address['public_key'], $address['memo']);
                    }
                }
            }

            $data['wallet']->network_name = api_settings_new($data['wallet']->network);
            $data['wallet']->coin_icon = empty($data['wallet']->coin_icon) ? '' : show_image_path($data['wallet']->coin_icon, 'coin/');

            $response = [
                'success' => true,
                'wallet' => $data['wallet'],
                'address' => isset($address['address']) ? $address['address'] : '',
                'token_address' => $data['wallet']['contract_address'] ?? '',
                'memo' => isset($address['memo']) ? $address['memo'] : '',
                'message' => __('Wallet found'),
                'data' => $resData
            ];
            unset($data['wallet']['contract_address']);
        } catch (\Exception $e) {
            storeException('walletDeposit -> ', $e->getMessage());
            $response = responseData(false);
        }

        return $response;
    }
    // user wallet list
    public function walletList($user_id)
    {
        $wallets = Wallet::where(['user_id' => $user_id])->orderBy('id', 'desc')->get();
        if (isset($wallets[0])) {
            foreach ($wallets as $wallet) {
                $wallet->address = $this->walletAddressList($wallet->id);
                $wallet->encrypt_id = encrypt($wallet->id);
            }
            $data = [
                'success' => true,
                'wallet_list' => $wallets,
                'message' => __('Data get successfully')
            ];
        } else {
            $data = [
                'success' => false,
                'wallet_list' => [],
                'message' => __('No data found')
            ];
        }

        return $data;
    }

    // wallet address list
    public function walletAddressList($wallet_id)
    {
        $addressList = [];
        $address = WalletAddressHistory::where(['wallet_id' => $wallet_id])->orderBy('id', 'desc')->get();
        if (isset($address[0])) {
            foreach ($address as $adrs) {
                $addressList[] = $adrs->address;
            }
        }

        return $addressList;
    }

    //create wallet
    public function createNewWallet($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];
        try {
            $data = [
                'user_id' => Auth::id(),
                'name' => $request->name,
                'status' => STATUS_SUCCESS,
                'balance' => 0
            ];
            $createWallet = Wallet::create($data);
            if ($createWallet) {
                $this->generateNewAddress($createWallet->id);

                $response = ['success' => true, 'message' => __('New wallet created successfully')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }

        return $response;
    }

    // generate new wallet address
    public function generateNewAddress($wallet_id)
    {
        $response = ['success' => false, 'address_list' => [], 'message' => __('Invalid request')];
        try {
            $wallet = new \App\Http\Services\wallet();
            $api = new BitCoinApiService(settings('coin_api_user'), settings('coin_api_pass'), settings('coin_api_host'), settings('coin_api_port'));
            $address = $api->getNewAddress();
            $generate = $wallet->AddWalletAddressHistory($wallet_id, $address);
            if ($generate) {

                $response = ['success' => true, 'address_list' => $this->walletAddressList($wallet_id), 'message' => __('Address generated successfully')];
            }
        } catch (\Exception $e) {
            $response = ['success' => false, 'address_list' => [], 'message' => $e->getMessage()];
        }

        return $response;
    }

    // wallet transaction history
    public function walletTransactionHistory($wallet_id)
    {
        $response = ['success' => false, 'transaction_list' => [], 'message' => __('Invalid request')];
        $id = app(MyCommonService::class)->checkValidId($wallet_id);

        if (is_array($id)) {
            $response = ['success' => false, 'message' => __('Item not found')];
            return response()->json($response);
        }
        $transactions = DepositeTransaction::where('sender_wallet_id', $id)
            ->orWhere('receiver_wallet_id', $id)
            ->orderBy('id', 'Desc')
            ->get();

        if (isset($transactions[0])) {
            foreach ($transactions as $tran) {
                $tran->fees = isset($tran->fees) ? $tran->fees : 0;
                $tran->sender_wallet_name = isset($tran->sender_wallet_id) ? $tran->senderWallet->name : '';
                $tran->receiver_wallet_name = isset($tran->receiver_wallet_id) ? $tran->receiverWallet->name : '';
                $tran->address_type = $tran->address_type == 'internal_address' ? addressType(ADDRESS_TYPE_EXTERNAL) : addressType($tran->address_type);
                $tran->transaction_type = $tran->receiver_wallet_id == $id ? DEPOSIT : WITHDRAWAL;
                $tran->status_text = deposit_status($tran->status);
            }
            $response = ['success' => true, 'transaction_list' => $transactions, 'message' => __('Data get successfully')];
        } else {
            $response = ['success' => false, 'transaction_list' => [], 'message' => __('Data not found')];
        }

        return $response;
    }

    // all activity history
    public function allActivityList()
    {
        $response = ['success' => false, 'activity_list' => (object) [], 'message' => __('Invalid request')];

        $transactions = DB::select("select wallets.name, case when sender_wallet_id=wallets.id then '2'
            when receiver_wallet_id=wallets.id then '1'
              else ''  end as transaction_type,deposite_transactions.created_at as date,
              deposite_transactions.amount as transaction_amount, deposite_transactions.status, wallets.name as wallet_name, deposite_transactions.amount,
              deposite_transactions.address_type
              from deposite_transactions
              join wallets on deposite_transactions.sender_wallet_id= wallets.id
                  or deposite_transactions.receiver_wallet_id = wallets.id
              where wallets.user_id=" . Auth::user()->id . "
                order by deposite_transactions.created_at desc");
        //        dd($transactions);

        $y = [];
        if (isset($transactions[0])) {
            foreach ($transactions as $key => $tran) {
                $y[date('d M y', strtotime($tran->date))][] = [
                    'wallet_name' => $tran->wallet_name,
                    'transaction_amount' => $tran->amount,
                    'address_type' => $tran->address_type == 'internal_address' ? addressType(ADDRESS_TYPE_EXTERNAL) : addressType($tran->address_type),
                    'transaction_type' => $tran->transaction_type,
                    'status_text' => deposit_status($tran->status),
                    'transaction_date' => date('d M y', strtotime($tran->date)),
                ];
            }
            $response = ['success' => true, 'activity_list' => $y, 'message' => __('Data get successfully')];
        } else {
            $response = ['success' => false, 'activity_list' => (object) [], 'message' => __('Data not found')];
        }

        return $response;
    }

    // check coin api mode
    public function get_rate_with_coin_api($from_coin_type, $to_coin_type, $amount)
    {
        try {
            $rate = convert_currency(1, $to_coin_type, $from_coin_type);
            if ($rate > 0) {
                $data['rate'] = $rate;
                $data['wallet_rate'] = bcmulx($rate, $amount, 8);
                $data['convert_rate'] = $data['wallet_rate'];
                return ['success' => true, 'data' => $data];
            } else {
                return ['success' => false, 'data' => []];
            }
        } catch (\Exception $e) {
            storeException('get_rate_with_coin_api exception ', $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    //get coin rate for wallet swaping
    public function get_wallet_rate($request)
    {
        $data['success'] = false;
        $data['message'] = __('Invalid request');
        try {
            $from_wallet = Wallet::where(['id' => $request->from_coin_id, 'user_id' => Auth::id()])->first();
            $to_wallet = Wallet::where(['id' => $request->to_coin_id, 'user_id' => Auth::id()])->first();

            $from_coin_type = $from_wallet->coin_type;
            $to_coin_type = $to_wallet->coin_type;
            $amount = isset($request->amount) ? $request->amount : 1;
            $response = $this->get_rate_with_coin_api($from_coin_type, $to_coin_type, $amount);

            if ($response['success'] == true) {
                $data['wallet_rate'] = $response['data']['wallet_rate'];
                $data['convert_rate'] = $response['data']['convert_rate'];
                $data['rate'] = $response['data']['rate'];
                $data['amount'] = $amount;
                $data['from_wallet'] = $from_wallet;
                $data['to_wallet'] = $to_wallet;
                $data['success'] = true;
                $data['message'] = __('success');
            } else {
                $data['success'] = false;
                $data['message'] = __('Rate calculation failed');
            }
        } catch (\Exception $e) {
            $data['success'] = false;
            $data['message'] = __('Something went wrong');
            storeException('coin rate exception= ', $e->getMessage());
        }

        return $data;
    }

    // coin swap process
    public function coinSwap($from_wallet, $to_wallet, $converted_amount, $requested_amount, $rate)
    {
        $data = ['success' => false, 'message' => __('Something went wrong')];
        try {
            DB::beginTransaction();
            if ($from_wallet->balance < $requested_amount) {
                $data = ['success' => false, 'message' => __("Wallet hasn't enough balance")];
                return $data;
            }
            if (!empty($from_wallet) && $from_wallet->coin_type == $to_wallet->coin_type) {
                $data = ['success' => false, 'message' => __('Can not swap to same wallet')];
                return $data;
            }

            $input = [
                'user_id' => $from_wallet->user_id,
                'from_wallet_id' => $from_wallet->id,
                'to_wallet_id' => $to_wallet->id,
                'from_coin_type' => $from_wallet->coin_type,
                'to_coin_type' => $to_wallet->coin_type,
                'requested_amount' => $requested_amount,
                'converted_amount' => $converted_amount,
                'rate' => $rate
            ];

            dispatch(new ConvertCoin($input, $from_wallet, $to_wallet))->onQueue('give-coin');

            $data = ['success' => true, 'message' => __('Wallet balance converted successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('coinSwap ex', json_encode($e->getMessage() . $e->getLine()));
            return $data;
        }

        DB::commit();
        return $data;
    }

    // get my wallet list with on order
    public function getMyWalletListWithOnorder($user_id, $paginate = null)
    {
        $dashboardRepo = new DashboardRepository();
        $wallets = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => $user_id, 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->orderBy('wallets.id', 'ASC')
            ->select('wallets.*', 'coins.coin_icon')
            ->paginate($paginate ?? 200);
        if (isset($wallets[0])) {
            foreach ($wallets as $wallet) {
                $wallet->available_balance = bcsubx($wallet->balance, $wallet->on_order, 8);
                $wallet->on_order = $dashboardRepo->getOnOrderBalance($wallet->coin_id, $user_id);

                $wallet->on_order_usd = get_coin_usd_value($wallet->on_order, $wallet->coin_type);
                $wallet->available_balance_usd = get_coin_usd_value($wallet->available_balance, $wallet->coin_type);
                $wallet->total_balance_usd = get_coin_usd_value($wallet->balance, $wallet->coin_type);
            }
        }
        return $wallets;
    }

    // get my wallet list with on order with total
    public function getMyWalletListWithOnOrderWithTotal($user_id, $paginate = null, $search = null)
    {
        $dashboardRepo = new DashboardRepository();
        $wallets = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => $user_id, 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('wallets.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('wallets.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('wallets.balance', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderByDesc('wallets.balance')
            ->select('wallets.*', 'coins.coin_icon', 'coins.is_withdrawal', 'coins.is_deposit', 'coins.trade_status', 'coins.currency_type')
            ->paginate($paginate ?? 200);

        $wallets->map(function ($wallet) use ($dashboardRepo, $user_id) {
            $wallet->on_order = $dashboardRepo->getOnOrderBalance($wallet->coin_id, $user_id);
            $wallet->available_balance = $wallet->balance;
            $wallet->total = bcaddx($wallet->on_order, $wallet->available_balance, 8);
            $wallet->on_order_usd = get_coin_usd_value($wallet->on_order, $wallet->coin_type);
            $wallet->available_balance_usd = get_coin_usd_value($wallet->available_balance, $wallet->coin_type);
            $wallet->total_balance_usd = get_coin_usd_value($wallet->total, $wallet->coin_type);
            $wallet->coin_icon = empty($wallet->coin_icon) ? '' : show_image_path($wallet->coin_icon, 'coin/');
        });

        $data['wallets'] = $wallets;

        return $data;
    }
    //get user wallet list only
    public function getUserWalletList($userId)
    {
        return Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => $userId, 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->orderBy('wallets.id', 'ASC')
            ->select('wallets.*')
            ->get();
    }
    // get my wallet list with on order with total
    public function getMyWalletListWithOnOrderWithTotalWithoutUSD($user_id, $paginate = null, $search = null)
    {
        $dashboardRepo = new DashboardRepository();
        $wallets = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => $user_id, 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->when(isset($search), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('wallets.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('wallets.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('wallets.balance', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderByDesc('wallets.balance')
            ->select('wallets.*', 'coins.coin_icon', 'coins.is_withdrawal', 'coins.is_deposit', 'coins.trade_status', 'coins.currency_type')
            ->paginate($paginate ?? 200);

        $wallets->map(function ($wallet) use ($dashboardRepo, $user_id) {
            $wallet->on_order = $dashboardRepo->getOnOrderBalance($wallet->coin_id, $user_id);
            $wallet->available_balance = $wallet->balance;
            $wallet->total = bcaddx($wallet->on_order, $wallet->available_balance, 8);
            $wallet->on_order_usd = get_coin_usd_value($wallet->on_order, $wallet->coin_type);
            $wallet->available_balance_usd = get_coin_usd_value($wallet->available_balance, $wallet->coin_type);
            $wallet->total_balance_usd = get_coin_usd_value($wallet->total, $wallet->coin_type);
            $wallet->coin_icon = empty($wallet->coin_icon) ? '' : show_image_path($wallet->coin_icon, 'coin/');
        });

        $data['wallets'] = $wallets;

        return $data;
    }

    // deposit network
    public function depositNetwotkAddress($wallet)
    {
        $response = responseData(false);
        try {
            if ($wallet->coin_type == 'USDT') {
                foreach (usdtWalletNetwork() as $key => $val) {
                    WalletNetwork::firstOrCreate(['wallet_id' => $wallet->id, 'network_type' => $key], ['coin_id' => $wallet->coin_id]);
                }
                $networks = WalletNetwork::where(['wallet_id' => $wallet->id])->orderBy('id', 'asc')->get();
                if (isset($networks[0])) {
                    foreach ($networks as $network) {
                        $network->network_name = usdtWalletNetwork($network->network_type);
                    }
                }
                $response = responseData(true, __('success'), $networks);
            }
        } catch (\Exception $e) {
            storeException('depositNetwotkAddress', $e->getMessage());
        }
        return $response;
    }

    public function walletInfo(int $userID, Coin|int|string $coin): Wallet
    {
        $coinInfo = (new AdminCoinRepository)->coinInfo($coin);

        return Wallet::firstOrCreate(['user_id' => $userID, 'coin_id' => $coinInfo->id], [
            'name' => $coinInfo->coin_type . ' wallet',
            'coin_type' => $coinInfo->coin_type
        ]);
    }
}
