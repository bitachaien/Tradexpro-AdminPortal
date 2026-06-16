<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\TradeFeeFinderRepositoryInterface;
use App\Dtos\TradeFeeDto;
use App\TradeFee;

class TradeFeeFinderRepositoy implements TradeFeeFinderRepositoryInterface
{
    public function __construct(
        private TradeFee $model
    ) {}

    public function findByCoinIdsAndUserId(int $baseCoinId, int $tradeCoinId, int $userId): ?TradeFeeDto
    {
        $result = $this->model->join('coin_pairs', 'trade_fees.coin_pair_id', '=', 'coin_pairs.id')
            ->where('trade_fees.user_id', $userId)
            ->where('coin_pairs.parent_coin_id', $baseCoinId)
            ->where('coin_pairs.child_coin_id', $tradeCoinId)
            ->where('trade_fees.status', TradeFee::STATUS_ACTIVE)
            ->select(
                'trade_fees.maker_fee AS maker_fee',
                'trade_fees.taker_fee AS taker_fee',
            )->first();

        return $result ? new TradeFeeDto($result->maker_fee, $result->taker_fee) : null;
    }

    public function findByCoinIds(int $baseCoinId, int $tradeCoinId): ?TradeFeeDto
    {
        $result = $this->model->join('coin_pairs', 'trade_fees.coin_pair_id', '=', 'coin_pairs.id')
            ->where('coin_pairs.parent_coin_id', $baseCoinId)
            ->where('coin_pairs.child_coin_id', $tradeCoinId)
            ->where('trade_fees.status', TradeFee::STATUS_ACTIVE)
            ->select(
                'trade_fees.maker_fee AS maker_fee',
                'trade_fees.taker_fee AS taker_fee',
            )->first();

        return $result ? new TradeFeeDto($result->maker_fee, $result->taker_fee) : null;
    }
}
