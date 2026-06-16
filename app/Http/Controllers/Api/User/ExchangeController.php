<?php

namespace App\Http\Controllers\Api\User;

use App\Exceptions\InvalidOrderTypeException;
use App\Exceptions\OrderNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;
use App\Http\Services\DashboardService;
use App\Http\Services\Logger;
use App\Http\Services\OrderDeletionService;
use App\Http\Services\SpotOrderDeletionService;
use App\Http\Services\TradingViewChartService;
use App\Http\Services\WebsocketServices\DataFetchers\UserOrderDataFetcher;
use App\Jobs\DeleteOrderJob;
use App\Model\AdminSetting;
use App\Model\CoinPair;
use App\Model\Transaction;
use App\Services\TradeSettingServices\TradeFeeFinderService;
use http\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExchangeController extends Controller
{
    private $service;
    private $coinPairService;
    public function __construct(
        private TradeFeeFinderService $tradeFeeFinderService
    )
    {
        $this->service = new DashboardService();
        $this->coinPairService = new CoinPairService();
    }

    public function appExchangeGetAllPair()
    {
        $pairservice = new CoinPairService();
        $pairs = $pairservice->getAllCoinPairs()['data'];
        return responseData(true, __('Success'), $pairs);
        return responseData(true, __('Success'), $pairs);
    }

    /**
     * specific exchange dashboard data
     * @param Request $request
     * @param $pair
     * @return array
     */
    public function appExchangeDashboard(Request $request, $pair = null)
    {

        $data['title'] = __('Exchange');
        $data['success'] = true;
        $data['message'] = __("Success");
        $data['broadcast_port'] = env('BROADCAST_PORT');
        $data['app_key'] = env('PUSHER_APP_KEY');
        $data['cluster'] = env('PUSHER_APP_CLUSTER');

        try {
            $pairservice = new CoinPairService();
            // if(Auth::guard('api')->check())  {
            //     create_coin_wallet(getUserId());
            // }
            $data['pair_status'] = false;
            if (isset($pair)) {
                $ar = explode('_', $pair);
                if (empty($request->base_coin_id) || empty($request->trade_coin_id)) {
                    $tradeCoinId = get_coin_id($ar[0]);
                    $baseCoinId = get_coin_id($ar[1]);

                    $pairData = checkPair($baseCoinId, $tradeCoinId);

                    if ($pairData) {
                        $data['pair_status'] = true;
                        $data['price'] = $pairData->price;
                        $request->merge([
                            'base_coin_id' => $baseCoinId,
                            'trade_coin_id' => $tradeCoinId,
                        ]);
                    } else {
                        // $firstPair = getFirstPair();
                        // if ($firstPair) {
                        //     $request->merge([
                        //         'base_coin_id' => $firstPair->parent_coin_id,
                        //         'trade_coin_id' => $firstPair->child_coin_id,
                        //     ]);
                        // } else {
                        //     $request->merge([
                        //         'base_coin_id' => $baseCoinId,
                        //         'trade_coin_id' => $tradeCoinId,
                        //     ]);
                        // }
                        $data['pairs'] = $pairservice->getAllCoinPairsForDashboard()['data'];
                        return $data;
                    }
                }
            } else {
                // $request->merge([
                //     'base_coin_id' => get_default_base_coin_id(),
                //     'trade_coin_id' => get_default_trade_coin_id(),
                // ]);
                $data['pairs'] = $pairservice->getAllCoinPairsForDashboard()['data'];
                return $data;
            }

            $request->merge([
                'dashboard_type' => 'dashboard'
            ]);
            if (checkPair($request->base_coin_id, $request->trade_coin_id)) {
                $pairservice = new CoinPairService();
                $data['pairs'] = $pairservice->getAllCoinPairsForDashboard()['data'];
                $data['order_data'] = $this->service->getOrderData($request)['data'];
                $data['fees_settings'] = $this->userFeesSettings(
                    $request->base_coin_id, $request->trade_coin_id
                );
                $data['last_price_data'] = $this->service->getDashboardMarketTradeDataTwo($request->base_coin_id, $request->trade_coin_id, 2);

            } else {
                $data['success'] = false;
                $data['message'] = __("Pair not found");
            }
        } catch (\Exception $e) {
            storeException('appExchangeDashboard', $e->getMessage());
            $data['success'] = false;
            $data['message'] = __("Something went wrong");
        }
        return $data;
    }

    // get fees settings
    public function userFeesSettings(
        int $baseCoinId, int $tradeCoinId
    )
    {
        if (Auth::guard('api')->check()) {
            $tradeFees = $this->tradeFeeFinderService->findTradeFee($baseCoinId, $tradeCoinId, getUserId());

            $fees = [
                'maker_fees' => $tradeFees->maker_fee,
                'taker_fees' => $tradeFees->taker_fee,
            ];
        } else {
            $fees = [];
        }
        return $fees;
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllOrdersApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message' => __('Something went wrong')
        ];
        try {
            $response = $this->service->getOrders($request)['data'];
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllBuyOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message' => __('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Buy Order History of ' . $request->trade_coin_type . '/' . $request->base_coin_type);
            $data['type'] = 'buy';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->service->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Buy Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getExchangeAllBuyOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeAllSellOrdersApp(Request $request)
    {
        $response = [
            'success' => false,
            'data' => [],
            'message' => __('Something went wrong')
        ];
        try {
            $data['title'] = __('All Open Sell Order History of ' . $request->trade_coin_type . '/' . $request->base_coin_type);
            $data['type'] = 'sell';
            $data['sub_menu'] = 'buy_order';
            $data['tradeCoinId'] = get_coin_id($request->trade_coin_type);
            $data['baseCoinId'] = get_coin_id($request->base_coin_type);
            $data['items'] = $this->service->getOrders($request)['data']['orders'];
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'All Sell Orders'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            storeException('getExchangeAllSellOrdersApp', $e->getMessage());
            return response()->json($response);
        }
    }

    public function getExchangeMarketTradesApp(Request $request): JsonResponse
    {
        return $this->handlerApiResponse(function () use ($request) {
            return $this->service->getMarketTransactions($request);
        });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyExchangeOrdersApp(Request $request, UserOrderDataFetcher $dataFetcher)
    {
        $request->validate([
            'base_coin_id' => 'required|int|exists:coins,id',
            'trade_coin_id' => 'required|int|exists:coins,id',
        ]);

        $data = [
            'success' => false,
            'data' => [],
            'message' => __('Something went wrong')
        ];
        try {
            $response = $dataFetcher->fetchData(
                $request->base_coin_id,
                $request->trade_coin_id,
                Auth::id()
            );
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Orders')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getMyExchangeOrders', $e->getMessage());
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyExchangeTradesApp(Request $request)
    {
        $data = [
            'success' => false,
            'data' => [],
            'message' => __('Something went wrong')
        ];
        try {
            $response = $this->service->getMyTradeHistory($request)['data'];
            $data = [
                'success' => true,
                'data' => $response,
                'message' => __('My Exchange Trades')
            ];
            return response()->json($data);
        } catch (\Exception $e) {
            storeException('getMyExchangeTrades', $e->getMessage());
            return response()->json($data);
        }
    }

    public function getExchangeChartDataApp(Request $request)
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
        $startTime = $request->input('start_time', (now()->subDays(10)->timestamp)); // 10 days ago in seconds
        $endTime = $request->input('end_time', (now()->timestamp));
        $chartService = new TradingViewChartService();
        if ($startTime >= $endTime) {
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


    public function deleteMyOrderApp(Request $request, SpotOrderDeletionService $spotOrderDeletionService)
    {
        $request->validate([
            'id' => 'required|int',
            'type' => 'required|in:buy,sell,stop',
        ]);

        try {
            $spotOrderDeletionService->checkAndDeleteOrder($request->id, Auth::id(), $request->type);
            return response()->json([
                'status' => true,
                'message' => __('order deleted successfully'),
            ]);
        } catch (InvalidOrderTypeException $exception) {
            return response()->json([
               'status' => false,
               'message' => $exception->getMessage(),
            ]);
        } catch (OrderNotFoundException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
             ]);
        } catch (\Throwable $exception) {
            storeException('deleteMyOrderApp', $exception->getMessage());
            return response()->json([
               'status' => false,
               'message' => __('Something went wrong.'),
            ]);
        }
    }
}
