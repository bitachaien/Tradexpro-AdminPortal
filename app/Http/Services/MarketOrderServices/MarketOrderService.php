<?php

namespace App\Http\Services\MarketOrderServices;

use App\Exceptions\CoinPairNotFoundException;
use App\Http\Services\CoinPairService;

class MarketOrderService
{
    public function __construct(private CoinPairService $coinPairService) {}

    public function getCurrentMarketPrice(int $baseCoinId, int $tradeCoinId) {
        
        $coinPairData = $this->coinPairService->getCoinPairsData($baseCoinId, $tradeCoinId);

        if (empty($coinPairData)) {
            throw new CoinPairNotFoundException('No coin pair found');
        }

        return $coinPairData->last_price;
    }
}
