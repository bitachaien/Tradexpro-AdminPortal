<?php

namespace App\Contracts\Repositories;

use App\Dtos\OrderCreationDto;
use App\Model\Buy;
use App\Model\Sell;
use Generator;
use Illuminate\Support\Collection;

interface TradableOrderRepositoryInterface extends ClosableOrderRepository
{
    public function create(OrderCreationDto $data): Buy | Sell;

    public function getById(int $id): Buy|Sell|null;

    public function findByIdAndLock(int $id): Buy|Sell|null;
    
    public function hasMoreCountThanLimit(int $baseCoinId, int $tradeCoinId, int $limit, bool $isBot): bool;

    /**
     * @throws \InvalidArgumentException
     */
    public function closeLastBotOrder(int $baseCoinId, int $tradeCoinId, string $orderDir): void;

    public function deleteBotOrders(int $baseCoinId, int $tradeCoinId, int $superAdminId): void;

    public function getTradableOrders(int $baseCoinId, int $tradeCoinId, int $limit = 0): Collection;
    
    public function getMatchedOrders(
        int $baseCoinId,
        int $tradeCoinId,
        int $isMarket,
        $price,
        string $orderByDirection,
        string $priceComparator,
    ): Generator;
}
