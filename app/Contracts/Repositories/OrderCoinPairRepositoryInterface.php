<?php

namespace App\Contracts\Repositories;

use App\Model\CoinPair;

interface OrderCoinPairRepositoryInterface
{
    public function findByCoinIds(int $parentCoinId, int $childCoinId): ?CoinPair;

    public function findById(int $id): ?CoinPair;
}
