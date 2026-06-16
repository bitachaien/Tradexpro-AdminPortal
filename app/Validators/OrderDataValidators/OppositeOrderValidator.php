<?php

namespace App\Validators\OrderDataValidators;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderValidatorDto;
use App\Exceptions\OrderException;
use Closure;

class OppositeOrderValidator
{
    const OPPOSITE_TYPES = [
        'buy' => 'sell',
        'sell' => 'buy'
    ];

    public function __construct(
        private OrderRepositoryFactoryInterface $orderRepositoryFactory
    ) {}

    public function handle(OrderValidatorDto $orderData, Closure $next)
    {
        $data = $orderData->data;
        $type = $orderData->type;

        if($data->is_market == 0)
        {
            return $next($orderData);
        }

        $oppositeOrders = $this->orderRepositoryFactory
            ->getOrderRepositoryByType(self::OPPOSITE_TYPES[$type])
            ->getTradableOrders($data->base_coin_id, $data->trade_coin_id, 1);

        if($oppositeOrders->count() == 0) 
        {
            throw new OrderException(__('No active '. self::OPPOSITE_TYPES[$type].' orders found'));
        }

        return $next($orderData);
    }
}
