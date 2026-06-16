<?php

namespace App\Services\Order;

use App\Dtos\OrderCreationDto;
use App\Exceptions\OrderException;
use App\Model\CoinPair;

class ToleranceCheckerService
{
    public function checkTolarence(OrderCreationDto $data, CoinPair $coinPair, string $type)
    {
        $settingTolerance = settings('trading_price_tolerance');

        if (bccomp($settingTolerance, '0', 2) <= 0) {
            return;
        }

        $lastPrice = $coinPair->price;

        if (bccomp($lastPrice, 0) <= 0) {
            return;
        }

        $tolerancePrice = bcdiv(bcmul($lastPrice, $settingTolerance), '100');
        $highTolerance = bcadd($lastPrice, $tolerancePrice);
        $lowTolerance = bcsub($lastPrice, $tolerancePrice);

        if (bccomp($data->price, $highTolerance) > 0 || bccomp($data->price, $lowTolerance) < 0) {

            throw new OrderException(__('The price must be between :lowTolerance and :highTolerance ', ['lowTolerance' => $lowTolerance, 'highTolerance' => $highTolerance]));
        }
    }
}
