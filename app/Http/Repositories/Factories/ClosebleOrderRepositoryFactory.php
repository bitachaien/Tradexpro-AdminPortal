<?php

namespace App\Http\Repositories\Factories;

use App\Contracts\Repositories\ClosableOrderRepository;
use App\Exceptions\InvalidOrderTypeException;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Model\Buy;
use App\Model\Sell;
use App\Model\StopLimit;

class ClosebleOrderRepositoryFactory
{
    public function getOrderRepository(string $type): ClosableOrderRepository
    {
        if (! in_array($type, ['buy', 'sell', 'stop'])) {
            throw new InvalidOrderTypeException('Invalid type '.$type);
        }

        if ($type == 'buy') {
            return new OrderRepository(new Buy);
        }

        if ($type == 'sell') {
            return new OrderRepository(new Sell);
        }

        return new StopLimitRepository(new StopLimit);
    }
}
