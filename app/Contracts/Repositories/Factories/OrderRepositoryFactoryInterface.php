<?php

namespace App\Contracts\Repositories\Factories;

use App\Contracts\Repositories\TradableOrderRepositoryInterface;
use InvalidArgumentException;

interface OrderRepositoryFactoryInterface
{
    /** 
    * @throws InvalidArgumentException
    */
    public function getOrderRepositoryByType(string $type): TradableOrderRepositoryInterface;
}
