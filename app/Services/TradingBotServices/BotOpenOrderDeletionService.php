<?php

namespace App\Services\TradingBotServices;

use App\Http\Repositories\Factories\OrderRepositoryFactory;

class BotOpenOrderDeletionService
{
    const SORT_DIRECTION = [
        'buy' => 'asc',
        'sell' => 'desc',
    ];

    public function __construct(
        private OrderRepositoryFactory $orderRepositoryFactory
    ) {}

    public function closeOpenBotOrders(
        string $type,
        int $baseCoinId,
        int $tradeCoinId,
        int $position = 100,
    ) {
        if (! array_key_exists($type, self::SORT_DIRECTION)) {
            throw new \InvalidArgumentException('Invalid order type. Expected "buy" or "sell".');
        }

        $orderRepository = $this->orderRepositoryFactory->getOrderRepositoryByType($type);

        if (! $orderRepository->hasMoreCountThanLimit($baseCoinId, $tradeCoinId, $position, true)) {
            return;
        }

        $orderRepository->closeLastBotOrder(
            $baseCoinId,
            $tradeCoinId,
            self::SORT_DIRECTION[$type]
        );

    }
}
