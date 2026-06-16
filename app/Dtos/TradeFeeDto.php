<?php

namespace App\Dtos;

class TradeFeeDto
{
    public function __construct(
        public float $maker_fee,
        public float $taker_fee,
    ) {}
}
