<?php

namespace App\Contracts\Repositories;

interface SellOrderRepositoryInterface
{
    public function getOnOrderBalanceByTradeCoinId(int $coinId, ?int $userId = null): ?string;
}
