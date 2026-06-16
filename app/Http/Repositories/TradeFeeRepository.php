<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\TradeFeeRepositoryInterface;
use App\Dtos\TradeFeeCreationDto;
use App\Dtos\TradeFeeUpdateDto;
use App\TradeFee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TradeFeeRepository implements TradeFeeRepositoryInterface
{
    public function __construct(
        private TradeFee $model
    ) {}

    public function getQuery(): Builder
    {
        return $this->model->query()
        ->leftJoin('users', 'trade_fees.user_id', '=', 'users.id')
        ->join('coin_pairs', 'trade_fees.coin_pair_id', '=', 'coin_pairs.id') 
        ->join('coins as child_coins', 'coin_pairs.child_coin_id', '=', 'child_coins.id')
        ->join('coins as parent_coins', 'coin_pairs.parent_coin_id', '=', 'parent_coins.id')
        ->select(
            'trade_fees.*',
            'users.email as user_email',
            'child_coins.coin_type as child_coin',
            'parent_coins.coin_type as parent_coin'
        );
    }

    public function create(TradeFeeCreationDto $data): TradeFee
    {
        return $this->model->create(get_object_vars($data));
    }

    public function changeStatusById(int $id)
    {
        $this->model->where('id', $id)->update([
            'status' => DB::raw('status ^ 1')
        ]);
    }

    public function insert(array $data)
    {
        return $this->model->insert($data);
    }

    public function update(int $id, TradeFeeUpdateDto $data)
    {
        $this->model->where('id', $id)
            ->update(get_object_vars($data));
    }

    public function findById(int $id): ?TradeFee
    {
        return $this->model->find($id);
    }

    public function countByUserIdAndCoinPairIds(array $coinPairIds, ?int $userId = null): int
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('coin_pair_id', $coinPairIds)
            ->count();
    }
}
