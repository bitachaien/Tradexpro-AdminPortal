<?php

namespace App\Contracts;

use App\Dtos\OrderCreationDto;

interface OrderServiceInterface
{
    public function placeOrder(OrderCreationDto $request, string $type);
}
