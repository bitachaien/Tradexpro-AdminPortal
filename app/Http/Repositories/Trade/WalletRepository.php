<?php

namespace App\Http\Repositories\Trade;

use App\Contracts\Repositories\FindableByUserIdAndCoinId;
use App\Model\UserWallet;

class WalletRepository implements FindableByUserIdAndCoinId
{
    public function __construct(private UserWallet $model) {}

    public function findByUserIdAndCoinId(int $userId, int $coinId): UserWallet
    {
        return $this->model->where([
            'user_id' => $userId,
            'coin_id' => $coinId,
        ])->firstOrFail();
    }
}
