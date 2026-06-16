<?php

namespace App\Http\Repositories;

use App\Contracts\Repositories\BuyOrderRepositoryInterface;
use App\Contracts\Repositories\SellOrderRepositoryInterface;
use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Model\SelectedCoinPair;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    private BuyOrderRepositoryInterface $buyOrderRepository;
    private SellOrderRepositoryInterface $sellOrderRepository;
    private StopLimitRepositoryInterface $stopLimitRepository;

    public function __construct()
    {
        $this->buyOrderRepository = app()->make(BuyOrderRepositoryInterface::class);
        $this->sellOrderRepository = app()->make(SellOrderRepositoryInterface::class);
        $this->stopLimitRepository = app()->make(StopLimitRepositoryInterface::class);
    }

    public function getOnOrderBalance($coinId, $userId = null): string
    {
        if ($userId == null) {
            $userId = getUserId();
        }

        $buyOnOrderBalance = $this->buyOrderRepository->getOnOrderBalanceByBaseCoinId($coinId, $userId);

        $sellOrderBalance = $this->sellOrderRepository->getOnOrderBalanceByTradeCoinId($coinId, $userId);

        $stopLimitBuyBalance = $this->stopLimitRepository->getOnOrderBalanceByBaseCoinId($coinId, $userId);

        $stopLimitSellBalance = $this->stopLimitRepository->getOnOrderBalanceByTradeCoinId($coinId, $userId);

        return bcaddx(
            $buyOnOrderBalance,
            bcaddx(
                $sellOrderBalance,
                bcaddx(
                    $stopLimitBuyBalance,
                    $stopLimitSellBalance
                )
            )
        );
    }
    public function getDocs($params = [], $select = null, $orderBy = [], $with = [])
    {
        if ($select == null) {
            $select = ['*'];
        }
        $query = SelectedCoinPair::select($select);
        foreach ($with as $wt) {
            $query = $query->with($wt);
        }
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $query->where($key, $value[0], $value[1]);
            } else {
                $query->where($key, '=', $value);
            }
        }
        foreach ($orderBy as $key => $value) {
            $query->orderBy($key, $value);
        }

        return $query->get();
    }

    public function updateWhere($where = [], $update = [])
    {
        $query = SelectedCoinPair::query();
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                $query->where($key, $value[0], $value[1]);
            } else {
                $query->where($key, '=', $value);
            }
        }
        return $query->update($update);
    }
}
