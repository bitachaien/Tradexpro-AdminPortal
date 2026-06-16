<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\MarketOverViewCoinPairRepositoryInterface;
use App\Model\CoinPair;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketOverviewCoinPairRepository implements MarketOverViewCoinPairRepositoryInterface
{
    public function __construct(
        private CoinPair $model
    ) {}

    public function getCoinPairs(int $limit, string $orderBy, string $orderByDirection): Collection
    {
        return $this->model->with(['parent_coin', 'child_coin'])
            ->orderBy($orderBy, $orderByDirection)
            ->limit($limit)
            ->get();
    }

    public function getTopCoinList(
        string $orderBy, 
        string $orderByDirection, 
        bool $isFutureTrade,
        $limit = 10,
        $offset = 1, 
        ?string $filter = null
    ): LengthAwarePaginator
    {
        $result = $this->model
            ->join(DB::raw('coins as child_coin'), 'coin_pairs.child_coin_id', '=', 'child_coin.id')
            ->join(DB::raw('coins as parent_coin'), 'coin_pairs.parent_coin_id', '=', 'parent_coin.id')
            ->leftJoin('wallets', 'parent_coin.id', '=', 'wallets.coin_id');

        if ($filter) {
            $result->where('child_coin.coin_type', 'like', '%'.$filter.'%')
                ->orWhere('parent_coin.coin_type', 'like', '%'.$filter.'%');
        }

        $result->selectRaw(
            'coin_pairs.id,
                coin_pairs.volume,
                coin_pairs.change,
                coin_pairs.high,
                coin_pairs.low,
                coin_pairs.price,
                child_coin.coin_icon as coin_icon,
                coin_pairs.created_at,
                parent_coin.coin_type as base_coin_type,
                child_coin.coin_type as coin_type,        
                COALESCE(SUM(wallets.balance), 0) as wallet_balance'
        )->groupBy(
            'coin_pairs.id',
            'child_coin.coin_icon',
            'coin_pairs.volume',
            'coin_pairs.change',
            'coin_pairs.high',
            'coin_pairs.low',
            'coin_pairs.price',
            'coin_pairs.created_at',
            'parent_coin.coin_type',
            'child_coin.coin_type'
        );

        if ($isFutureTrade) {
            $result->where('enable_future_trade', STATUS_ACTIVE);
        }

        return $result->orderBy($orderBy, $orderByDirection)->paginate(
            $limit, ['*'], 'page', $offset
        );
    }
}
