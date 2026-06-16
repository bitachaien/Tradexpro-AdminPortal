<?php

namespace App\Dtos;

class TradeFeeCreationDto
{
    public function __construct(
        public ?int $user_id,
        public int $coin_pair_id,
        public float $maker_fee,
        public float $taker_fee,
    ) {}
}
