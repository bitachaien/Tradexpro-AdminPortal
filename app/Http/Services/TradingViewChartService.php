<?php

namespace App\Http\Services;

use App\Http\Repositories\TradingViewChartRepository;
use App\Model\FifteenMinute;
use App\Model\FiveMinute;
use App\Model\FourHour;
use App\Model\OneDay;
use App\Model\ThirtyMinute;
use App\Model\TwoHour;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TradingViewChartService
{
    const INTERVAL_TO_MODEL_MAPPING = [
        '5' => FiveMinute::class,
        '15' => FifteenMinute::class,
        '30' => ThirtyMinute::class,
        '120' => TwoHour::class,
        '240' => FourHour::class,
        '1440' => OneDay::class,
    ];

    private TradingViewChartRepository $repository;

    public function __construct()
    {
        $this->repository = app()->make(TradingViewChartRepository::class);
    }

    public function getChartData(int $startTime, int $endTime, int $interval, int $baseCoinId, int $tradeCoinId, ?int $trade = null)
    {
        return $this->repository->getChartData(
            $this->getModel($interval),
            $baseCoinId,
            $tradeCoinId,
            $startTime,
            $endTime,
            $trade
        );
    }

    private function getModel(int $interval)
    {
        if (! array_key_exists($interval, self::INTERVAL_TO_MODEL_MAPPING)) {
            throw new InvalidArgumentException("Invalid interval: {$interval}");
        }

        return app()->make(self::INTERVAL_TO_MODEL_MAPPING[$interval]);
    }

    public function updateCandleData($transaction)
    {
        $price = $transaction->price;
        $volume = $transaction->total;
        $baseCoinId = $transaction->base_coin_id;
        $tradeCoinId = $transaction->trade_coin_id;

        $transactionTime = strtotime($transaction->created_at);

        foreach (self::INTERVAL_TO_MODEL_MAPPING as $interval => $model) {
            $interval = $transactionTime - ($transactionTime % ($interval * 60));
            $this->insertCandle(
                app()->make($model),
                $price,
                $volume,
                $baseCoinId,
                $tradeCoinId,
                $interval
            );
        }
    }

    public function insertCandle(Model $model, $price, $volume, int $baseCoinId, int $tradeCoinId, int $intervalTime)
    {
        $candle = $this->repository->getCandle($model,
            ['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId, 'interval' => ['>=' ,$intervalTime]]
        )->first();

        $lastCandle = $model->where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])
            ->orderBy('interval', 'DESC')->first();

        if (is_null($candle)) {
            $open = is_null($lastCandle) ? $price : $lastCandle->close;
            $close = $price;
            $high = $price > $open ? $price : $open;
            $low = $price < $open ? $price : $open;
            $data = [
                'base_coin_id' => $baseCoinId,
                'trade_coin_id' => $tradeCoinId,
                'interval' => $intervalTime, 'open' => $open,
                'volume' => $volume,
                'close' => $close,
                'high' => $high,
                'low' => $low,
            ];

            $this->repository->createNewCandle($model, $data);

            return;
        }

        $close = $price;
        $high = $candle->high < $price ? $price : $candle->high;
        $low = $candle->low > $price ? $price : $candle->low;
        $volume = $candle->volume + $volume;
        $this->repository->updateCandle($model,
            ['id' => $candle->id],
            ['close' => $close, 'high' => $high, 'low' => $low, 'volume' => $volume]
        );
    }
}
