<?php

namespace App\Dtos;

use App\Model\CoinPair;

class OrderValidatorDto
{
    public function __construct(
        public OrderCreationDto $data,
        public CoinPair $coinPair,
        public string $type
    ) {}
}
