<?php

namespace App\Contracts\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MarketOverViewCoinPairRepositoryInterface
{
    public function getCoinPairs(int $limit, string $orderBy, string $orderByDirection): Collection;

    public function getTopCoinList(
        string $orderBy,
        string $orderByDirection,
        bool $isFutureTrade,
        $limit = 10,
        $offset = 1,
        ?string $filter = null
    ): LengthAwarePaginator;
}
