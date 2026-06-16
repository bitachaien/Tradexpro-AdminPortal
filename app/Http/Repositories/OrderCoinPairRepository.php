<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\OrderCoinPairRepositoryInterface;
use App\Model\CoinPair;

class OrderCoinPairRepository implements OrderCoinPairRepositoryInterface
{
    public function __construct(
        private CoinPair $model
    ) {}

    public function findByCoinIds(int $parentCoinId, int $childCoinId): ?CoinPair
    {
        return $this->model->where('parent_coin_id', $parentCoinId)
            ->where('child_coin_id', operator: $childCoinId)
            ->first();
    }

    public function findById(int $id): ?CoinPair
    {
        return $this->model->find($id);
    }
}
