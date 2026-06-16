<?php

namespace App\Contracts\Repositories;

interface BuyOrderRepositoryInterface
{
    public function getOnOrderBalanceByBaseCoinId(int $coinId, ?int $userId = null): string;
}
