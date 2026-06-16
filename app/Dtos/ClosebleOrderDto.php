<?php

namespace App\Dtos;

use App\Model\Buy;
use App\Model\Sell;
use App\Model\StopLimit;

class ClosebleOrderDto
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $base_coin_id,
        public int $trade_coin_id,
        public $price,
        public $amount,
        public $processed,
        public string $type,
        public $maker_fees,
        public $taker_fees
    ) {}

    public static function fromOrderAndType(Buy | Sell | StopLimit $order, string $type): ClosebleOrderDto
    {
        return new self(
            $order->id,
            $order->user_id,
            $order->base_coin_id,
            $order->trade_coin_id,
            $type == 'stop' ? $order->limit_price : $order->price,
            $order->amount,
            $type == 'stop' ? 0 : $order->processed,
            $type == 'stop' ? $order->order : $type,
            $order->maker_fees,
            $order->taker_fees
        );
    }
}
