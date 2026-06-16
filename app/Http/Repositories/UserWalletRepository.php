<?php

namespace App\Http\Repositories;


use App\Model\UserWallet;
use Illuminate\Support\Facades\DB;

class UserWalletRepository extends CommonRepository
{

    function __construct($model)
    {
        parent::__construct($model);
    }
    /**
     * @param array $userWallet
     * @return bool according to creation status
     */
    public function create($userWallet)
    {
        if (!empty($userWallet)) {
            return Userwallet::insert($userWallet);
        } else {
            return false;
        }
    }

    /**
     * @param $userId
     * @return UserWallet of all coins by retrieving it using $userId
     */
    public function getUserWalletBalance($userId)
    {
        $wallets = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
            ->where('user_id', $userId)
            ->join('coins', ['wallets.coin_id' => 'coins.id'])
            ->get();

        return $wallets;
    }

    /**
     * @param $userId
     * @return UserWallet by retrieving it using $userId
     */
    public function getUserWalletBalanceQuery($userId)
    {
        $wallets = UserWallet::select(
            'wallets.id',
            'coins.coin_type',
            'coins.name',
            'wallets.balance',
            'wallets.address',
            'wallets.created_at',
            'wallets.updated_at'
        )
            ->where('user_id', $userId)
            ->join('coins', ['wallets.coin_id' => 'coins.id']);

        return $wallets;
    }

    /**
     * @param $userId
     * @param $coinId
     * @return UserWallet of a specific coin by retrieving it using $userId and $coinId
     */
    public function getUserSingleWalletBalance($userId, $coinId)
    {
        $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
            ->join('coins', ['wallets.coin_type' => 'coins.coin_type'])
            ->where(['wallets.user_id' => $userId, 'wallets.is_primary' => STATUS_ACTIVE, 'coins.id' => $coinId])
            ->first();

        if (empty($wallet))
            $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
                ->join('coins', ['wallets.coin_type' => 'coins.coin_type'])
                ->where(['wallets.user_id' => $userId, 'coins.id' => $coinId])
                ->orderBy('balance', 'desc')
                ->first();

        return $wallet;
    }

    public function getWalletAndLock(int $userId, int $coinId)
    {
        $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
            ->join('coins', ['wallets.coin_type' => 'coins.coin_type'])
            ->where(['wallets.user_id' => $userId, 'wallets.is_primary' => STATUS_ACTIVE, 'coins.id' => $coinId])
            ->lockForUpdate()
            ->first();

        if (empty($wallet))
            $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
                ->join('coins', ['wallets.coin_type' => 'coins.coin_type'])
                ->where(['wallets.user_id' => $userId, 'coins.id' => $coinId])
                ->orderBy('balance', 'desc')
                ->lockForUpdate()
                ->first();

        return $wallet;
    }

    /**
     * @param $userId
     * @return UserWallet -> primary wallet of the $userId
     */
    public function getUserPrimaryWallet($userId)
    {
        $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
            ->where(['user_id' => $userId, 'coins.is_primary' => 1])
            ->join('coins', ['wallets.coin_id' => 'coins.id'])
            ->first();

        return $wallet;
    }

    /**
     * @param $userId
     * @param $coinId
     * @return UserWallet balance of multiple $coinId for $userId
     */
    public function getUserMultipleWalletBalance($userId, $coinId)
    {
        $wallet = UserWallet::select('wallets.id as wallet_id', 'wallets.*', 'coins.*')
            ->where(['user_id' => $userId])
            ->whereIn('coin_id', $coinId)
            ->join('coins', ['wallets.coin_id' => 'coins.id'])
            ->get();

        return $wallet;
    }

    /**
     * @param $userId
     * @param $coinId
     * @param $amount
     * @return bool according to the deduction operation
     */

    public function deductBalanceByOld($userId, $coinId, $amount)
    {
        try {
            $wallet = $this->getUserSingleWalletBalance($userId, $coinId);
            UserWallet::where(['user_id' => $userId, 'coin_id' => $coinId])->update(['balance' => bcsubx($wallet->balance, $amount)]);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return false;
        }
        return true;
    }


    public function deductBalanceById($wallet, $amount, $column = 'balance')
    {
        try {
            UserWallet::where(['id' => $wallet->wallet_id, 'user_id' => $wallet->user_id, 'coin_type' => $wallet->coin_type])->decrement($column, $amount);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @param $userId
     * @param $coinId
     * @param $amount
     * @return bool according to balance add operation
     */

    public function addBalanceById($userId, $coinId, $amount)
    {
        try {
            UserWallet::where(['user_id' => $userId, 'coin_id' => $coinId])->increment('balance', $amount);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            return false;
        }
        return true;
    }
    /**
     * @param $user_id
     * @return UserWallet of the transferable coins
     */
    public function getTransferableCoinList($user_id)
    {
        return UserWallet::where('user_id', $user_id)
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where('coins.is_transferable', 1)
            ->select(
                'wallets.id as wallet_id',
                'wallets.user_id',
                'wallets.coin_id',
                'wallets.balance',
                'wallets.balance_betting',
                'wallets.balance_bot',
                'wallets.balance_referral',
                'coins.coin_type',
                'coins.full_name',
                DB::raw("TRUNCATE(wallets.balance + wallets.balance_betting +
                                            wallets.balance_bot + wallets.balance_referral, 8) as total")
            )
            ->get();
    }

    /**
     * @param $user_id
     * @param $coin_id
     * @return UserWallet for $user_id and $coin_id
     */
    public function getUserWallet($user_id, $coin_id)
    {
        return UserWallet::where('user_id', $user_id)
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where('coins.id', $coin_id)
            ->select('wallets.*')
            ->first();
    }

    /**
     * @param $userId
     * @param null $search
     * @return UserWallet except the hidden wallets
     */
    public function getAllUserWallets($userId, $search = null)
    {
        $data = UserWallet::where('user_id', $userId)
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->select(
                'wallets.id as wallet_id',
                'wallets.user_id',
                'wallets.coin_id',
                'wallets.balance',
                'wallets.address',
                'wallets.balance_betting',
                'wallets.balance_bot',
                'wallets.balance_referral',
                'coins.coin_type',
                'coins.full_name',
                'coins.coin_icon',
                'coins.withdrawal_status',
                'coins.deposit_status',
                'coins.is_currency',
                DB::raw("TRUNCATE(wallets.balance + wallets.balance_betting + wallets.balance_bot + wallets.balance_referral, 8) as total,
                CASE
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 60
                   THEN CONCAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at)),\" seconds ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 60 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 3600
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/60), \" minutes ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 3600 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 86400
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/3600), \" hours ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 86400 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 2592000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/86400), \" days ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 2592000 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 31536000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/2592000), \" months ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 31536000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/31536000), \" years ago\")
                END as time")
            )
            ->doesnthave('hiddenWallets')
            ->where(['coins.status' => 1]);

        if (!empty($search)) {
            $data = $data->where(function ($query) use ($search) {
                $query->where('coins.coin_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('coins.full_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('wallets.balance', 'LIKE', '%' . $search . '%');
            });
        }

        return $data->get();
    }

    /**
     * @param $userId
     * @param null $search
     * @return UserWallet of the hidden wallets
     */
    public function getHiddenWallets($userId, $search = null)
    {
        $data = UserWallet::where('user_id', $userId)
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->select(
                'wallets.id as wallet_id',
                'wallets.user_id',
                'wallets.coin_id',
                'wallets.balance',
                'wallets.address',
                'wallets.balance_betting',
                'wallets.balance_bot',
                'wallets.balance_referral',
                'coins.coin_type',
                'coins.full_name',
                'coins.coin_icon',
                'coins.withdrawal_status',
                'coins.deposit_status',
                'coins.is_currency',
                DB::raw("TRUNCATE(wallets.balance + wallets.balance_betting + wallets.balance_bot + wallets.balance_referral, 8) as total,
                CASE
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 60
                   THEN CONCAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at)),\" seconds ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 60 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 3600
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/60), \" minutes ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 3600 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 86400
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/3600), \" hours ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 86400 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 2592000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/86400), \" days ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 2592000 AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) < 31536000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/2592000), \" months ago\")
                   WHEN UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at) >= 31536000
                   THEN CONCAT(FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(wallets.updated_at))/31536000), \" years ago\")
                END as time")
            )
            ->whereHas('hiddenWallets')
            ->where(['coins.status' => 1]);

        if (!empty($search)) {
            $data = $data->where(function ($query) use ($search) {
                $query->where('coins.coin_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('coins.full_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('wallets.balance', 'LIKE', '%' . $search . '%');
            });
        }

        return $data->get();
    }
}
