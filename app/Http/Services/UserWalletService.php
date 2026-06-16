<?php

namespace App\Http\Services;


use App\Http\Repositories\UserWalletRepository;
use App\Model\Coin;
use App\Model\Wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserWalletService extends CommonService
{
    public $model = Wallet::class;
    public $repository = UserWalletRepository::class;
    public $logger;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->logger = app(Logger::class);
    }

    public function create($userId, $coinId = null)
    {
        $userWallet = [];
        if (empty($coinId)) {
            $coins = Coin::all();
            foreach ($coins as $coin) {
                $userWallet[] = [
                    'user_id' => $userId,
                    'coin_id' => $coin->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        } else {
            $coin = Coin::where('id', $coinId)->first();
            if (!empty($coin)) {
                $userWallet = [
                    'user_id' => $userId,
                    'coin_id' => $coinId,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            } else {
                return false;
            }
        }
        $createdUserWallet = $this->object->create($userWallet);

        return $createdUserWallet;
    }
    public function createAllUserWallet($coinId)
    {
        $userWallet = [];
        $users = User::with('userWallets')->where(['role' => USER_ROLE_USER])->get();
            foreach ($users as $user) {
                $flag = true;
                foreach ($user->userWallets as $wallet){
                    if($wallet->coin_id == $coinId){
                        $flag = false;
                    }
                }
                if($flag){
                    $userWallet[] = [
                        'user_id' => $user->id,
                        'coin_id' => $coinId,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

            }
        $createdUserWallet = $this->object->create($userWallet);
        return $createdUserWallet;
    }

    public function deductBalanceById($userId, $coinId, $amount)
    {
        return $this->object->deductBalanceById($userId, $coinId, $amount);
    }

    public function addBalanceById($user_id, $coin_id, $amount)
    {
        return $this->object->addBalanceById($user_id, $coin_id, $amount);
    }

    public function getBalance($userId, $coinId = null)
    {
        if (!is_int($userId) && (!is_int($coinId) || !is_null($coinId))) {
            return [];
        }

        if (empty($coinId)) {
            $balance = $this->object->getUserWalletBalance($userId);
        } elseif (is_array($coinId)) {
            $balance = $this->object->getUserMultipleWalletBalance($userId, $coinId);
        } else {
            $balance = $this->object->getUserSingleWalletBalance($userId, $coinId);
        }

        return json_encode($balance);
    }

// User's primary Wallet
    public function getPrimaryWallet($userId)
    {
        $wallet = '';
        if (!empty($userId) && is_int($userId)) {
            $wallet = $this->object->getUserPrimaryWallet($userId);
        }

        return json_encode($wallet);

    }

// craete address
    public function createAddress()
    {
        $coinApiCredentials = Coin::join('coin_settings', 'coin_settings.')->first();

        $address = '';
        return json_encode($address);
    }

// get wallet and balance query

    public function getBalanceQuery($userId)
    {
        $balance = [];
        if (!empty($userId)) {
            $balance = $this->object->getUserWalletBalanceQuery($userId);
        }
        return $balance;
    }

    public function transferableCoinList($user)
    {
        $response = ['status' => false, 'data' => [], 'message' => 'no.data.found.'];
        $wallets = [];
        if (!empty($user)) {
            $wallets = $this->object->getTransferableCoinList($user->id);
            if ($wallets->count() > 0) {
                $response['status'] = true;
                $response['data']['wallets'] = $wallets;
                $response['message'] = 'transferable.wallet.list.';
            }
        }

        return $response;
    }


    public function depositReport($user, $request = null)
    {
        $response = ['status' => true, 'data' => [], 'message' => __('Deposit list.')];

        $paginationCount = user_pagination_count();
        $deposits = Deposit::join('user_wallets', 'deposits.user_wallet_id', '=', 'user_wallets.id')
            ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
            ->join('users', 'user_wallets.user_id', '=', 'users.id')
            ->where('users.id', $user->id)
            ->doesnthave('systemDepositAdjustment')
            ->select('deposits.*', 'coins.coin_type', 'user_wallets.address as address');

        if (!empty($request->walletId)) {
            $deposits = $deposits->where('user_wallets.id', $request->walletId);
        }
        if (!empty($request->search)) {
            $search = $request->search;
            $deposits = $deposits->where(function ($query) use ($search) {
                $query->where('coins.coin_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('user_wallets.address', 'LIKE', '%' . $search . '%')
                    ->orWhere('deposits.transaction_hash', 'LIKE', '%' . $search . '%');
            });
        }
        $deposits = $deposits->orderBy('id', 'DESC')->paginate($paginationCount)->appends($request->all());
        if ($deposits->count() < 1) {
            $response['status'] = false;
            $response['message'] = 'no.deposit.record.is.found.';
        } else {
            $deposits->each(function ($item) {
                $item->user_wallet_id = encrypt($item->user_wallet_id);
            });
            $response['data']['deposits'] = $deposits;
        }

        return $response;
    }

    public function withdrawalReport($user, $request = null)
    {
        $response = ['status' => true, 'data' => [], 'message' => 'Withdrawal.list.'];

        $paginationCount = user_pagination_count();
        $withdrawals = Withdrawal::join('user_wallets', 'withdrawals.user_wallet_id', '=', 'user_wallets.id')
            ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
            ->join('users', 'user_wallets.user_id', '=', 'users.id')
            ->where('users.id', $user->id)
            ->doesnthave('systemWithdrawalAdjustment')
            ->select('withdrawals.*', 'coins.coin_type', DB::raw('(
                CASE
                WHEN withdrawals.status = 0 THEN "Waiting"
                WHEN withdrawals.status = 1 THEN "Success"
                WHEN withdrawals.status = 2 THEN "Pending"
                WHEN withdrawals.status = 9 THEN "Processing"
                ELSE "Canceled"
                END) AS status'));

        if (!empty($request->walletId)) {
            $withdrawals = $withdrawals->where('user_wallets.id', $request->walletId);
        }
        if (!empty($request->search)) {
            $search = $request->search;
            $status = strtolower($search);
            if (strpos('success', $status) !== false) {
                $withdrawals = $withdrawals->where(function ($query) use ($search) {
                    $query->where('withdrawals.status', '1')
                        ->orWhere('coins.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.address', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.transaction_hash', 'LIKE', '%' . $search . '%');
                });
            } elseif (strpos('pending', $status) !== false) {
                $withdrawals = $withdrawals->where(function ($query) use ($search) {
                    $query->where('withdrawals.status', '2')
                        ->orWhere('coins.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.address', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.transaction_hash', 'LIKE', '%' . $search . '%');
                });
            } elseif (strpos('canceled', $status) !== false) {
                $withdrawals = $withdrawals->where(function ($query) use ($search) {
                    $query->where('withdrawals.status', '3')
                        ->orWhere('coins.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.address', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.transaction_hash', 'LIKE', '%' . $search . '%');
                });
            } else {
                $withdrawals = $withdrawals->where(function ($query) use ($search) {
                    $query->where('coins.coin_type', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.address', 'LIKE', '%' . $search . '%')
                        ->orWhere('withdrawals.transaction_hash', 'LIKE', '%' . $search . '%');
                });
            }
        }
        $withdrawals = $withdrawals->orderBy('id', 'DESC')->paginate($paginationCount)->appends($request->all());

        if ($withdrawals->count() < 1) {
            $response['status'] = false;
            $response['message'] = 'no.withdrawal.record.is.found.';
        } else {
            $withdrawals->each(function ($item) {
                $item->user_wallet_id = encrypt($item->user_wallet_id);
                if($item->status == 'Pending'){
                    $item->wid = encrypt($item->id);
                }else{
                    $item->wid = '';
                }
            });
            $response['data']['withdrawals'] = $withdrawals;
        }

        return $response;
    }

    public function pendingCoinReport($user, $search = null)
    {
        $response = ['status' => true, 'data' => [], 'message' => 'pending.coin.list.'];
        $paginationCount = user_pagination_count();
        $pendingCoins = PendingCoin::join('user_wallets', 'pending_coins.user_wallet_id', '=', 'user_wallets.id')
            ->join('coins', 'user_wallets.coin_id', '=', 'coins.id')
            ->join('users', 'user_wallets.user_id', '=', 'users.id')
            ->where('users.id', $user->id)
            ->where('pending_coins.status', 0)
            ->select('pending_coins.*', 'coins.coin_type', 'user_wallets.address as address');

        if (!empty($search)) {
            $pendingCoins = $pendingCoins->where(function ($query) use ($search) {
                $query->where('coins.coin_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('user_wallets.address', 'LIKE', '%' . $search . '%')
                    ->orWhere('pending_coins.transaction_hash', 'LIKE', '%' . $search . '%');
            });
        }
        $pendingCoins = $pendingCoins->orderBy('id', 'DESC')->paginate($paginationCount);
        if ($pendingCoins->count() < 1) {
            $response['status'] = false;
            $response['message'] = 'no.pending.coin.request.is.found.';
        } else {
            $pendingCoins->each(function ($item) {
                $item->user_wallet_id = encrypt($item->user_wallet_id);
            });
            $response['data']['pendingCoins'] = $pendingCoins;
        }

        return $response;
    }

    /**
     * For get all wallets of a user
     * @param null $userId
     * @param null $search
     * @return array
     */
    public function getUserWallets($userId = null, $search = null)
    {
        try {
            if (empty($userId)) {
                $userId = Auth::id();
            }
            $data['wallets'] = $this->object->getAllUserWallets($userId, $search);
            $data['wallets']->each(function ($wallet) {
                if (!empty($wallet->coin_icon)) {
                    $wallet->coin_icon = getImageUrl(coinIconPath() . $wallet->coin_icon);
                } else {
                    $wallet->coin_icon = null;
                }

                if ($wallet->coin_type == "AMZ") {
	                $wallet->deposit_address = env("AMZ_USER");
	                $wallet->address = env("AMZ_USER");
	                $memo = $this->getWalletAddress($wallet->wallet_id);  // Added
	                if (isset($memo['status']) && $memo['status']) {
		                $wallet->memo = $memo['data']['memo'];
		                $wallet->memo_status = true;
	                } else {
		                $wallet->memo = '';
		                $wallet->memo_status = false;
	                }
                } else if($wallet->coin_type == "EOS") {
		            $wallet->deposit_address = env("EOS_USER");
		            $wallet->address = env("EOS_USER");
	                $memo = $this->getWalletAddress($wallet->wallet_id);  // Added
	                if(isset($memo['status']) && $memo['status']) {
		                $wallet->memo = $memo['data']['memo'];
		                $wallet->memo_status = true;
	                }
	                else {
		                $wallet->memo = '';
		                $wallet->memo_status = false;
	                }
	            }
            });

            return [
                'status' => true,
                'message' => '',
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => 'something.went.wrong',
                'data' => []
            ];
        }
    }

    /**
     * Take wallet id as parameter
     * @param $walletId
     * If no address exists generate new address and return it at DB,
     * else it fetch address from DB and return it
     * @return array
     */
    public function getWalletAddress($walletId)
    {
        if (!$wallet = UserWallet::where(['id' => $walletId, 'user_id' => Auth::id()])->first()) {
	        return [
		        'status'  => false,
		        'message' => 'wallet.not.found',
		        'data'    => []
	        ];
        }

        $coin = Coin::select('coins.coin_type', 'coins.deposit_status', 'coin_settings.api_service')->where('coins.id', $wallet->coin_id)->join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')->first();
        if ($coin) {
            if (!$coin->deposit_status) {
                return [
                    'status' => false,
                    'message' => 'deposit.is.disabled',
                    'data' => []
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => 'deposit.is.disabled',
                'data' => []
            ];
        }

        if (empty($wallet)) {
            return [
                'status' => false,
                'message' => 'invalid.wallet',
                'data' => []
            ];
        }

	    $data['coin_type'] = $coin->coin_type;
        if (!empty($wallet->address)) {
            $data['address'] = $wallet->address;
	        $data['memo'] = $wallet->address;        // for backward compatibility
        } else {
            $service = app('CoinApiService', [$coin->api_service, $coin->coin_type]);
            $newAddress = $service->getNewAddress();
            if ($newAddress == false) {
                return [
                    'status' => false,
                    'message' => 'something.went.wrong',
                    'data' => []
                ];
            }
            $data['address'] = $newAddress;
            $data['memo'] = $newAddress;        // for backward compatibility
            $wallet->address = $newAddress;
            $wallet->update();
        }

        return [
            'status' => true,
            'message' => '',
            'data' => $data
        ];
    }

    /**
     * Hide selected wallets of an user
     * @param $selectedWallets
     * @return array
     */
    public function hideWallet($selectedWallets)
    {
        $user = Auth::user();
        $wallets = UserWallet::where('user_id', $user->id)->whereIn('id', $selectedWallets)->doesnthave('hiddenWallets')->get();
        $numberOfWallets = $wallets->count();

        if ($numberOfWallets != count($selectedWallets)) {
            return [
                'status' => false,
                'message' => 'invalid.wallet.id',
                'data' => []
            ];
        }
        $insert = [];
        DB::beginTransaction();
        try {
            foreach ($wallets as $wallet) {
                $insert[] = [
                    'user_id' => $user->id,
                    'user_wallet_id' => $wallet->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
            HiddenWallet::insert($insert);
            app(UserService::class)->addUserActivityLog('Hide Wallet', $user);
            DB::commit();

            return [
                'status' => true,
                'message' => 'wallets.are.hidden.successfully',
                'data' => []
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'something.went.wrong',
                'data' => []
            ];
        }
    }

    /**
     * Show hidden wallets
     * @param $selectedWallets
     * @return array
     */
    public function unHideWallet($selectedWallets)
    {
        $user = Auth::user();
        $wallets = HiddenWallet::where('user_id', $user->id)->whereIn('user_wallet_id', $selectedWallets)->get();
        $numberOfWallets = $wallets->count();

        if ($numberOfWallets != count($selectedWallets)) {
            return [
                'status' => false,
                'message' => 'invalid.wallet.id',
                'data' => []
            ];
        }
        DB::beginTransaction();
        try {
            foreach ($wallets as $wallet) {
                $wallet->delete();
            }
            app(UserService::class)->addUserActivityLog('Show Wallet', $user);
            DB::commit();

            return [
                'status' => true,
                'message' => 'wallets.are.unhidden.successfully',
                'data' => []
            ];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'something.went.wrong',
                'data' => []
            ];
        }
    }

    public function getHiddenWallets($userId = null, $search = null)
    {
        try {
            if (empty($userId)) {
                $userId = Auth::id();
            }
            $data['hiddenWallets'] = $this->object->getHiddenWallets($userId, $search);

            return [
                'status' => true,
                'message' => '',
                'data' => $data
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => 'something.went.wrong',
                'data' => []
            ];
        }
    }




}
