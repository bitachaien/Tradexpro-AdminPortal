<?php

namespace App\Services\Order;

use App\Contracts\Repositories\OrderCoinPairRepositoryInterface;
use App\Dtos\OrderCreationDto;
use App\Dtos\OrderValidatorDto;
use App\Exceptions\OrderException;
use App\Model\CoinPair;
use Illuminate\Pipeline\Pipeline;

class OrderValidationService
{
    public function __construct(
        private OrderCoinPairRepositoryInterface $orderCoinPairRepository,
    ) {}

    public function validateOrder(OrderCreationDto $data, string $type)
    {
        $coinPair = $this->getCoinPair($data->base_coin_id, $data->trade_coin_id, $type);

        app()->make(Pipeline::class)
            ->send(
                new OrderValidatorDto($data, $coinPair, $type)
            )->through(
                config('order_validation.validators')
            )->thenReturn();
    }

    private function getCoinPair(int $baseCoinId, int $tradeCoinId, string $type): CoinPair
    {
        if ($baseCoinId == $tradeCoinId) {
            throw new OrderException(__('Base coin and trade coin should be different'));
        }

        $coinPair = $this->orderCoinPairRepository->findByCoinIds($baseCoinId, $tradeCoinId);

        if (! $coinPair) {
            throw new OrderException(__('Invalid '.$type.' order request'));
        }

        return $coinPair;
    }
}
