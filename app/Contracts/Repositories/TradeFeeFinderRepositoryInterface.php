<?php

namespace App\Contracts\Repositories;

use App\Dtos\TradeFeeDto;

interface TradeFeeFinderRepositoryInterface
{
    public function findByCoinIdsAndUserId(int $baseCoinId, int $tradeCoinId, int $userId): ?TradeFeeDto;

    public function findByCoinIds(int $baseCoinId, int $tradeCoinId): ?TradeFeeDto;
}
