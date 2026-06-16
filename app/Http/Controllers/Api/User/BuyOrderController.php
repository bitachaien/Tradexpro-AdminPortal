<?php

namespace App\Http\Controllers\Api\User;

use App\Dtos\Factories\OrderCreationDtoFactory;
use App\Http\Controllers\Api\Traits\OrderTrait;
use App\Http\Controllers\Controller;
use App\Http\Services\BuyOrderService;
use App\Http\Services\StopLimitService;
use App\Http\Validators\BuyMarketOrderValidator;
use App\Http\Validators\BuyOrderValidator;
use App\Http\Validators\StopLimitValidators;
use App\Services\Order\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BuyOrderController extends Controller
{

    use OrderTrait;

    /**
     * Place limit buy order
     * @param BuyOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyLimitOrderApp(
        BuyOrderValidator $request,
        OrderService $service,
        OrderCreationDtoFactory $orderCreationDtoFactory
    )
    {
        $request->merge([
            'is_market' => 0
        ]);

        return $this->getOrderResponse(
            function() use ($request, $service, $orderCreationDtoFactory) {
                $service->checkAndCreateOrder($orderCreationDtoFactory->getDto($request, auth()->id()), 'buy');    
            },
            'Buy limit'
        );
    }

    /**
     * place market buy order
     * @param BuyOrderValidator $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyMarketOrderApp(
        BuyMarketOrderValidator $request,
        OrderService $service,
        OrderCreationDtoFactory $orderCreationDtoFactory
    )
    {
        $request->merge([
            'is_market' => 1
        ]);

        return $this->getOrderResponse(
            function() use ($request, $service, $orderCreationDtoFactory) {
                $service->checkAndCreateOrder($orderCreationDtoFactory->getDto($request, auth()->id()), 'buy');    
            },
            'Buy market'
        );
    }

    /**
     * place stop limit buy order
     * @param StopLimitValidators $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBuyStopLimitOrderApp(StopLimitValidators $request)
    {
        $request->merge([
            'order'=>'buy'
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
