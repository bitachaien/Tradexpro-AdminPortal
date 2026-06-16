<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\BotCoinPairRepositoryInterface;
use App\Dtos\BotCoinPairDto;
use App\Model\CoinPair;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BotCoinPairRepository implements BotCoinPairRepositoryInterface
{
    public function __construct(private CoinPair $model)
    {
    }

    public function getBotStatusActivePairs(): Collection
    {
        $coinPairs = $this->model
            ->select(
                'coin_pairs.id',
                'parent_coin_id as base_coin_id',
                'child_coin_id as trade_coin_id',
                'coin_pairs.is_token',
                'coin_pairs.bot_trading',
                'coin_pairs.initial_price',
                'coin_pairs.bot_possible',
                'coin_pairs.bot_operation',
                'coin_pairs.bot_percentage',
                'coin_pairs.upper_threshold',
                'coin_pairs.lower_threshold',
                DB::raw('visualNumberFormat(price) as last_price'),
                'child_coin.coin_type as trade_coin_type',
                'parent_coin.coin_type as base_coin_type',
                'child_coin.coin_price as trade_coin_usd_rate',
                'parent_coin.coin_price as base_coin_usd_rate',
                DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
            )
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->where(['coin_pairs.status' => STATUS_ACTIVE, 'coin_pairs.bot_trading' => STATUS_ACTIVE])
            ->orderBy('coin_pairs.id', 'asc')
            ->get();

        $result = collect([]);

        foreach($coinPairs as $coinPair) {
            $result->push(BotCoinPairDto::fromCoinPair($coinPair));
        }

        return $result;
    }

    public function getSingleCoinPairById(int $id): ?BotCoinPairDto
    {
        $coinPair = $this->model
            ->select(
                'coin_pairs.id',
                'parent_coin_id as base_coin_id',
                'child_coin_id as trade_coin_id',
                'coin_pairs.is_token',
                'coin_pairs.bot_trading',
                'coin_pairs.initial_price',
                'coin_pairs.bot_possible',
                'coin_pairs.bot_operation',
                'coin_pairs.bot_percentage',
                'coin_pairs.upper_threshold',
                'coin_pairs.lower_threshold',
                DB::raw('visualNumberFormat(price) as last_price'),
                'child_coin.coin_type as trade_coin_type',
                'parent_coin.coin_type as base_coin_type',
                'child_coin.coin_price as trade_coin_usd_rate',
                'parent_coin.coin_price as base_coin_usd_rate',
                DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
            )
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->where(['coin_pairs.status' => STATUS_ACTIVE, 'coin_pairs.bot_trading' => STATUS_ACTIVE, 'coin_pairs.id' => $id])
            ->orderBy('coin_pairs.id', 'asc')
            ->first();

        return $coinPair ? BotCoinPairDto::fromCoinPair($coinPair) : null;
    }
}
