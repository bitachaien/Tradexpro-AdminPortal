<?php

namespace App\Dtos;

class OrderProcessingDTO
{
    public function __construct(
        public int $orderId,
        public int $matchedOrderId,
        public string $type,
    ) {}
}
