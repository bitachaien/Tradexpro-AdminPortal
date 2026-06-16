<?php

namespace App\Contracts\Repositories;

use App\Model\StopLimit;

interface StopLimitRepositoryInterface
{
    public function getOnOrderBalanceByBaseCoinId(int $coinId, ?int $userId = null): ?string;

    public function getOnOrderBalanceByTradeCoinId(int $coinId, ?int $userId = null): ?string;

    public function findByIdAndLock(int $id): ?StopLimit;

    public function findByCoinIdsAndUser(int $baseCoinId, int $tradeCoinId, int $userId): array;

}
