<?php

namespace App\Dtos;

use App\Model\CoinPair;

class BotCoinPairDto
{
    public function __construct(
        public int $id,
        public int $base_coin_id,
        public int $trade_coin_id,
        public int $is_token,
        public int $bot_trading,
        public $initial_price,
        public int $bot_possible,
        public string $bot_operation,
        public $bot_percentage,
        public $upper_threshold,
        public $lower_threshold,
        public $last_price,
        public string $trade_coin_type,
        public string $base_coin_type,
        public $trade_coin_usd_rate,
        public $base_coin_usd_rate,
        public string $pair_bin
    ) {}

    public static function fromCoinPair(CoinPair $coinPair): BotCoinPairDto
    {
        return new self(
            $coinPair->id,
            $coinPair->base_coin_id,
            $coinPair->trade_coin_id,
            $coinPair->is_token,
            $coinPair->bot_trading,
            $coinPair->initial_price,
            $coinPair->bot_possible,
            $coinPair->bot_operation,
            $coinPair->bot_percentage,
            $coinPair->upper_threshold,
            $coinPair->lower_threshold,
            $coinPair->last_price,
            $coinPair->trade_coin_type,
            $coinPair->base_coin_type,
            $coinPair->trade_coin_usd_rate,
            $coinPair->base_coin_usd_rate,
            $coinPair->pair_bin
        );
    }
}
