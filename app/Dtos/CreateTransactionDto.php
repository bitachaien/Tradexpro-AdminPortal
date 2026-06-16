<?php

namespace App\Dtos;

class CreateTransactionDto
{
    public function __construct(
        public ?string $transaction_id,
        public int $base_coin_id,
        public int $trade_coin_id,
        public int $buy_id,
        public int $sell_id,
        public int $buy_user_id,
        public int $sell_user_id,
        public string $price_order_type,
        public $amount,
        public $price,
        public $btc_rate,
        public $total,
        public $buy_fees,
        public $sell_fees,
        public $bot_order,
        public $btc
    ) {}
}
