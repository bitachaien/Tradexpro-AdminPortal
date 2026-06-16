<?php

namespace App\Dtos;

use App\Model\CoinPair;

class Calculate24HourPriceDto
{
    public function __construct(
        public int $parent_coin_id,
        public int $child_coin_id,
        public float $price,
    ) {}

    public static function fromCoinPair($pair): self
    {
        return new self(
            parent_coin_id: $pair->parent_coin_id,
            child_coin_id: $pair->child_coin_id,
            price: $pair->last_price ?: $pair->price,
        );
    }

    public static function fromArrayCoinPair($pair): self
    {
        return new self(
            parent_coin_id: $pair['parent_coin_id'],
            child_coin_id: $pair['child_coin_id'],
            price: $pair['last_price'],
        );
    }
}
