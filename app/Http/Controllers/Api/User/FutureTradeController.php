<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\FutureTradeBalanceTransferRequest;
use App\Http\Requests\FutureTradeCloseOrderRequest;
use App\Http\Requests\FutureTradeOrderDetailsBuySellRequest;
use App\Http\Requests\FutureTradePlacedBuySellOrderRequest;
use App\Http\Requests\FutureTradePrePlaceOrderDataRequest;
use App\Http\Requests\FutureTradeUpdateProfitLossRequest;
use App\Http\Services\BuyOrderService;
use App\Http\Services\SellOrderService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\FutureTradeService;
use App\Http\Services\FutureTradeBuySellOrderService;
use App\Http\Services\CoinPairService;
use App\Http\Services\DashboardService;
use App\Http\Services\TradingViewChartService;
use Stripe\Service\Issuing\TransactionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FutureTradeController extends Controller
{
    private $futureTradeService;
    private $futureTradeBuySellOrderService;

    public function __construct()
    {
        $this->futureTradeService = new FutureTradeService;
        $this->futureTradeBuySellOrderService = new FutureTradeBuySellOrderService;
    }
    public function commonSettings()
    {

        $data = [];
        $response = responseData(true, __('Future Trade settings'), $data);

        return $response;
    }

    public function walletList(Request $request)
    {
        $response = $this->futureTradeService->getWalletList($request);

        return response()->json($response);
    }

    public function walletBalanceTransfer(FutureTradeBalanceTransferRequest $request)
    {
        $response = $this->futureTradeService->balanceTransfer($request);

        return response()->json($response);
    }

    public function walletTransferHistory(Request $request)
    {
        $response = $this->futureTradeService->walletTransferHistory($request);

        return response()->json($response);
    }

    public function coinPairList(Request $request)
    {
        $response = $this->futureTradeService->coinPairList($request);

        return response()->json($response);
    }

    public function prePlaceOrderData(FutureTradePrePlaceOrderDataRequest $request)
    {
        $response = $this->futureTradeService->prePlaceOrderData($request);

        return response()->json($response);
    }

    public function placedBuyOrder(FutureTradePlacedBuySellOrderRequest $request)
    {
        $response = $this->futureTradeBuySellOrderService->placedBuyOrder($request);

        return response()->json($response);
    }

    public function updateProfitLossLongShortOrder(FutureTradeUpdateProfitLossRequest $request)
    {
        $response = $this->futureTradeBuySellOrderService->updateProfitLossLongShortOrder($request);
        return response()->json($response);
    }

    public function placedSellOrder(FutureTradePlacedBuySellOrderRequest $request)
    {
        $response = $this->futureTradeBuySellOrderService->placedSellOrder($request);

        return response()->json($response);
    }

    public function canceledLongShortOrder(FutureTradeOrderDetailsBuySellRequest $request)
    {
        $user = auth()->user();
        $response = $this->futureTradeBuySellOrderService->canceledLongShortOrder($request, $user);

        return response()->json($response);
    }

    public function getLongShortPositionOrderList(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getLongShortPositionOrderList($request);

        return response()->json($response);
    }

    public function getLongShortOpenOrderList(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getLongShortOpenOrderList($request);

        return response()->json($response);
    }

    public function orderDetails(FutureTradeOrderDetailsBuySellRequest $request)
    {
        $response = $this->futureTradeBuySellOrderService->orderDetails($request);

        return response()->json($response);
    }

    public function getFutureAllOrdersApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getOrders($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => 'All Orders'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getFutureAllOrdersApp', $e->getMessage());
            return response()->json($data);
        }
    }

    public function appFutureTradeGetAllPair()
    {
        $pairservice = new CoinPairService();
        $pairs = $pairservice->getAllCoinPairs()['data'];
        return responseData(true,__('Success'),$pairs);
    }

    public function appFutureTradeDashboard(Request $request, $pair=null)
    {
        $data['title'] = __('Exchange');
        $data['success'] = true;
        $data['message'] = __("Success");
        $data['broadcast_port'] = env('BROADCAST_PORT');
        $data['app_key'] = env('PUSHER_APP_KEY');
        $data['cluster'] = env('PUSHER_APP_CLUSTER');
        if(Auth::guard('api')->check())  {
            create_future_wallet(getUserId());
        }
        $data['pair_status'] = true;
        if(isset($pair)) {
            $ar =  explode('_',$pair);
            if (empty($request->base_coin_id) || empty($request->trade_coin_id)) {
                $tradeCoinId = get_coin_id($ar[0]);
                $baseCoinId = get_coin_id($ar[1]);

                if (checkFuturePair($baseCoinId,$tradeCoinId)) {
                    $request->merge([
                        'base_coin_id' => $baseCoinId,
                        'trade_coin_id' => $tradeCoinId,
                    ]);
                } else {
                    $data['pair_status'] = false;
                    $firstPair = getFirstPair();
                    if ($firstPair) {
                        $request->merge([
                            'base_coin_id' => $firstPair->parent_coin_id,
                            'trade_coin_id' => $firstPair->child_coin_id,
                        ]);
                    } else {
                        $request->merge([
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                        ]);
                    }
                }
            }
        } else {
            $request->merge([
                'base_coin_id' => get_default_base_coin_id(),
                'trade_coin_id' => get_default_trade_coin_id(),
            ]);
        }

        $request->merge([
            'dashboard_type' => 'dashboard'
        ]);

        if (checkFuturePair($request->base_coin_id,$request->trade_coin_id)) {
            $pairservice = new CoinPairService();
            $data['pairs'] = $pairservice->getAllFutureCoinPairs()['data'];
            $data['order_data'] = $this->futureTradeBuySellOrderService->getOrderData($request)['data'];
            $data['fees_settings'] = $this->userFeesSettings();
            $data['last_price_data'] = $this->futureTradeBuySellOrderService->getDashboardMarketTradeDataTwo($request->base_coin_id, $request->trade_coin_id,2);

        } else {
            $data['success'] = false;
            $data['message'] = __("Pair not found");
        }
        return $data;
    }

    public function userFeesSettings()
    {
        if(Auth::guard('api')->check())  {
            $fees = calculated_fee_limit(getUserId());
        } else {
            $fees = [];
        }
        return $fees;
    }

    public function getFutureTradeMarketTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getMarketTransactions($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message'=>'All Market Trades'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getExchangeMarketOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getFutureTradeChartDataApp(Request $request)
    {
        $service = new DashboardService();
        if (empty($request->base_coin_id) || empty($request->trade_coin_id)) {
            $tradeCoinId = $service->_getTradeCoin();
            $baseCoinId = $service->_getBaseCoin();
            $request->merge([
                'base_coin_id' => $baseCoinId,
                'trade_coin_id' => $tradeCoinId,
            ]);
        }
        $interval = $request->input('interval', 1440);
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        $startTime = $request->input('start_time', strtotime(now()) - 864000);
        $endTime = $request->input('end_time', strtotime(now()));
        $chartService = new TradingViewChartService();
        if($startTime >= $endTime){
            return response()->json([
                'success' => false,
                'message' => __('start.time.is.always.big.than.end.time')
            ]);
        }
        $data = $chartService->getChartData($startTime, $endTime, $interval, $baseCoinId, $tradeCoinId);

        $response = [
            'success' => true,
            'message' => __('Success'),
            'dataType' => 'own',
            'data' => $data
        ];
        return $response;
    }

    public function getFutureTradeAllBuyOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Buy Order History of '.$request->trade_coin_type.'/'.$request->base_coin_type);
            $data['type'] = 'buy';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->futureTradeBuySellOrderService->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Buy Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getFutureTradeAllBuyOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    public function getFutureTradeAllSellOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Sell Order History of '.$request->trade_coin_type.'/'.$request->base_coin_type);
            $data['type'] = 'sell';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->futureTradeBuySellOrderService->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Sell Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getFutureTradeAllSellOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    public function getFutureTradeOrdersApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getMyOrders($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Orders')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getFutureTradeOrdersApp', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getFutureTradeMyExchangeTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getMyTradeHistory($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Trades')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getFutureTradeMyExchangeTradesApp', $e->getMessage());
            return response()->json($data);
        }
    }

    public function deleteFutureTradeMyOrderApp(Request $request)
    {
        $dashboardService = new DashboardService();
        $response = $dashboardService->deleteOrder($request);

        return response()->json($response);
    }

    public function getFutureTradeAllOrdersHistoryBuyApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $data['title'] = __('Buy Order History');
        $buyService = new BuyOrderService();
        $data['type'] = 'buy';
        $data['sub_menu'] = 'buy_order';
        $data['items'] = $buyService->getAllOrderHistory($order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Buy Order History')
        ];
        return response()->json($response);
    }

    public function getFutureTradeAllOrdersHistorySellApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $data['title'] = __('Sell Order History');
        $data['type'] = 'sell';
        $data['sub_menu'] = 'sell_order';
        $sellService = new SellOrderService();
        $data['items'] = $sellService->getAllOrderHistory($order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Sell Order History')
        ];
        return response()->json($response);
    }

    public function getFutureTradeAllTransactionHistoryApp(Request $request)
    {
        $limit = $request->per_page ?? 5;
        $order_data['column_name'] = $request->column_name ?? '';
        $order_data['order_by'] = $request->order_by ?? '';
        $data['title'] = __('Transaction History');
        $data['sub_menu'] = 'transaction';
        $sellService = new \App\Http\Services\TransactionService();
        $data['items'] = $sellService->getMyAllTransactionHistory(Auth::id(),$order_data)->paginate($limit);
        $response = [
            'success' => true,
            'data' => $data,
            'message'=>__('Transaction History')
        ];
        return response()->json($response);
    }

    public function getFutureTradeExchangeAllOrdersApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getOrders($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => 'All Orders'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getExchangeAllOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getFutureTradeExchangeMarketTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message'=>__('Something went wrong')
        ];
        try {
            $response = $this->futureTradeBuySellOrderService->getMarketTransactions($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message'=>'All Market Trades'
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getExchangeMarketOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getFutureTradeMarketPairData(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getFutureTradeMarketPairData($request);

        return response()->json($response);
    }

    public function getFutureTradeOrderCalculation(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getFutureTradeOrderCalculation($request);

        return response()->json($response);
    }

    public function closeLongShortOrder(FutureTradeCloseOrderRequest $request)
    {
        $user = auth()->user();

        $response = $this->futureTradeBuySellOrderService->closeLongShortOrder($request, $user);

        return response()->json($response);
    }

    public function getLongShortOrderHistory(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getLongShortOrderHistory($request);

        return response()->json($response);
    }

    public function getLongShortTransactionHistory(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getLongShortTransactionHistory($request);

        return response()->json($response);
    }

    public function getLongShortTradeHistory(Request $request)
    {
        $response = $this->futureTradeBuySellOrderService->getLongShortTradeHistory($request);

        return response()->json($response);
    }

    public function getFutureTradeExchangeMarketDetailsApp(Request $request)
    {
        return response()->json(
            $this->futureTradeService->getFutureTradeExchangeMarketDetailsApp($request)
        );
    }

    public function closeLongShortAllOrders(Request $request)
    {
        return response()->json(
            $this->futureTradeBuySellOrderService->closeLongShortAllOrders($request)
        );
    }

    public function test()
    {
        $i = User::count()+10;
        for($i; $i< $i*100; $i++)
        {
            $user = new User;
            $user->email = 'user'.$i.'@email.com';
            $user->first_name = Str::random(5);
            $user->last_name = Str::random(5);
            $user->role = USER_ROLE_USER;
            $user->status = STATUS_SUCCESS;
            $user->is_verified = 1;
            $user->password = Hash::make('123456');
            $user->save();

            create_coin_wallet($user->id);
        }
    }
}
