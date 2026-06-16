<?php

namespace App\Services\TradeSettingServices;

use App\Contracts\Repositories\TradeFeeFinderRepositoryInterface;
use App\Dtos\TradeFeeDto;

class TradeFeeFinderService
{
    public function __construct(
        private TradeFeeFinderRepositoryInterface $tradeFeeFinderRepository 
    ) {}

    public function findTradeFee(int $baseCoinId, int $tradeCoinId, int $userId): TradeFeeDto
    {
        $result = $this->tradeFeeFinderRepository->findByCoinIdsAndUserId($baseCoinId, $tradeCoinId, $userId);

        if($result) {
            return $result;
        }

        $result = $this->tradeFeeFinderRepository->findByCoinIds($baseCoinId, $tradeCoinId);

        if($result) {
            return $result;
        }

        $fees = calculated_fee_limit($userId);

        return new TradeFeeDto($fees['maker_fees'], $fees['taker_fees']);
    }
}
