<?php

namespace App\Contracts\Repositories;

use App\Dtos\CreateTransactionDto;
use App\Model\Transaction;
use Illuminate\Support\Collection;

interface TradeTransactionRepositoryInterface
{
    public function create(CreateTransactionDto $createTransactionDto): Transaction;

    public function findByCoinIdsAndUserId(int $baseCoinIds, int $tradeCoinId, int $userId, int $limit = 20): Collection;
}
