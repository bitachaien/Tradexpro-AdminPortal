<?php

namespace App\Http\Repositories\Factories;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Contracts\Repositories\TradableOrderRepositoryInterface;
use App\Http\Repositories\OrderRepository;
use App\Model\Buy;
use App\Model\Sell;
use InvalidArgumentException;

class OrderRepositoryFactory implements OrderRepositoryFactoryInterface
{

    public function getOrderRepositoryByType(string $type): TradableOrderRepositoryInterface
    {
        if (!in_array($type, ['buy', 'sell'])) {
            throw new InvalidArgumentException('Invalid type ' . $type);
        }

        return new OrderRepository($this->getModel($type));
    }

    private function getModel(string $type): Buy|Sell
    {
        if ($type == 'buy') {
            return new Buy();
        }

        return new Sell();
    }
}
