<?php

namespace App\Dtos\Factories;

use App\Dtos\OrderCreationDto;
use App\Http\Services\MarketOrderServices\MarketOrderService;
use App\Services\TradeSettingServices\TradeFeeFinderService;
use Illuminate\Http\Request;

class OrderCreationDtoFactory
{
    public function __construct(
        private MarketOrderService $marketOrderService,
        private TradeFeeFinderService $tradeFeeFinderService
    ) {
    }

    public function getDto(Request $request, int $userId): OrderCreationDto
    {
        $temporaryFees = $this->tradeFeeFinderService->findTradeFee(
            $request->base_coin_id,
            $request->trade_coin_id, 
            $userId);

        $request->merge([
            'maker_fees' => custom_number_format($temporaryFees->maker_fee),
            'taker_fees' => custom_number_format($temporaryFees->taker_fee),
            'btc_rate' => getBtcRate($request->trade_coin_id),
            'user_id' => $userId
        ]);

        if($request->is_market && $request->is_market == 1)
        {
            $request->merge([
                'price' => $this->marketOrderService->getCurrentMarketPrice(
                    $request->get('base_coin_id'),
                    $request->get('trade_coin_id')
                )
            ]);
        }

        return OrderCreationDto::fromRequest($request);
    }
}
