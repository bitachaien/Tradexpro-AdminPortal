<?php

namespace App\Http\Controllers\Api\User;

use App\Dtos\Factories\OrderCreationDtoFactory;
use App\Http\Controllers\Api\Traits\OrderTrait;
use App\Http\Controllers\Controller;
use App\Http\Services\SellOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Validators\MarketSellOrderValidator;
use App\Http\Validators\SellOrderValidator;
use App\Http\Validators\StopLimitValidators;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Throwable;

class SellOrderController extends Controller
{

    use OrderTrait;

    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellLimitOrderApp(
        SellOrderValidator $request,
        OrderService $service,
        OrderCreationDtoFactory $orderCreationDtoFactory
    )
    {
        $request->merge([
            'is_market' => 0
        ]);

        return $this->getOrderResponse(
            function() use ($request, $service, $orderCreationDtoFactory) {
                $service->checkAndCreateOrder($orderCreationDtoFactory->getDto($request, auth()->id()), 'sell');    
            },
            'Sell limit'
        );
    }

    /**
     * @param SellOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeSellMarketOrderApp(
        MarketSellOrderValidator $request,
        OrderService $service,
        OrderCreationDtoFactory $orderCreationDtoFactory
    )
    {
        $request->merge([
            'is_market' => 1
        ]);

        return $this->getOrderResponse(
            function() use ($request, $service, $orderCreationDtoFactory) {
                $service->checkAndCreateOrder($orderCreationDtoFactory->getDto($request, auth()->id()), 'sell');    
            },
            'Sell market'
        );
    }

    /**
     * @param StopLimitValidators $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeStopLimitSellOrderApp(StopLimitValidators $request)
    {
        $request->merge([
            'order'=>'sell'
        ]);
        if ($request->trade_coin_id == $request->base_coin_id) {
            response()->json( [
                'status' => false,
                'message' => __('Base coin and trade coin should be different'),
            ]);
        }
        $service = new StopLimitService();
        $response =  $service->create($request);
        return response()->json($response);
    }
}
