<?php

namespace App\Dtos;

use Illuminate\Http\Request;

class OrderCreationDto
{
    public function __construct(
        public int $user_id,
        public int $trade_coin_id,
        public int $base_coin_id,
        public $amount,
        public $processed,
        public float $virtual_amount,
        public $price,
        public float $btc_rate,
        public int $is_market,
        public int $category,
        public $maker_fees,
        public $taker_fees,
        public int $is_conditioned,
        public int $is_bot = 0
    ) {}

    public static function fromRequest(Request $request): OrderCreationDto
    {
        return new self(
            $request->user_id,
            $request->trade_coin_id,
            $request->base_coin_id,
            $request->get('amount'),
            $request->get('processed', 0),
            $request->get('amount') * random_int(20, 80) / 100,
            $request->price,
            $request->btc_rate,
            $request->is_market || 0,
            $request->get('category', 1),
            $request->maker_fees,
            $request->taker_fees,
            $request->get('is_conditioned', 0),
            $request->is_bot || 0,
        );
    }
}
