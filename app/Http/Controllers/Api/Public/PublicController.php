<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;
use App\Http\Services\DashboardService;
use App\Http\Services\PublicService;
use App\Http\Services\TradingViewChartService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service = new PublicService();
    }
    // get exchange price
    public function getExchangePrice(Request $request,$pair=null)
    {
        $response = responseData(false,__('Invalid request'),);
        try {
            if(isset($pair)) {
                if(strpos($pair,'_')) {
                    $ar =  explode('_',$pair);
                    $tradeCoinId = get_coin_id_test(strtoupper($ar[0]));
                    $baseCoinId = get_coin_id_test(strtoupper($ar[1]));
                    if ($baseCoinId == 0 || $tradeCoinId == 0) {
                        return response()->json(responseData(false,__('Invalid symbol')));
                    }
                    $request->merge([
                        'base_coin_id' => $baseCoinId,
                        'trade_coin_id' => $tradeCoinId,
                    ]);
                    $data = $this->getMarketPriceDataSet($baseCoinId,$tradeCoinId);

                    if ($data) {
                        $response = responseData(true,__('success'),$data);
                    } else {
                        $response = responseData(false,__('No data found'),$data);
                    }
                } else {
                    $response = responseData(false,__('The pair should be like -> BTC_USDT'),);
                }
            } else {
                $data = $this->getMarketPriceDataPair();
                if ($data) {
                    $response = responseData(true,__('success'),$data);
                } else {
                    $response = responseData(false,__('No data found'),$data);
                }
            }
        } catch (\Exception $e) {
            $response = responseData(false,__('Something went wrong'),);
        }
        return response()->json($response);
    }

    // get order book
    public function getExchangeOrderBook(Request $request,$pair)
    {
        $response = responseData(false,__('Invalid request'),);
        try {
            if(strpos($pair,'_')) {
                $ar =  explode('_',$pair);
                $tradeCoinId = get_coin_id_test(strtoupper($ar[0]));
                $baseCoinId = get_coin_id_test(strtoupper($ar[1]));
                if ($baseCoinId == 0 || $tradeCoinId == 0) {
                    return response()->json(responseData(false,__('Invalid symbol')));
                }
                if ($request->type) {
                    $type = $request->type;
                } else {
                    $type = 'buy_sell';
                }
                if ($request->limit) {
                    $limit = $request->limit;
                } else {
                    $limit = 10;
                }
                $request->merge([
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                    'order_type' => $type,
                    'dashboard_type' => 'dashboard',
                    'per_page' => $limit
                ]);
                $data = $this->service->getOrderdata($request);

                if ($data) {
                    $response = responseData(true,__('success'),$data);
                } else {
                    $response = responseData(false,__('No data found'),$data);
                }
            } else {
                $response = responseData(false,__('The pair should be like -> BTC_USDT'),);
            }
        } catch (\Exception $e) {
            $response = responseData(false,__('Something went wrong'),);
        }
        return response()->json($response);
    }

    // get trade
    public function getExchangeTrade(Request $request,$pair)
    {
        $response = responseData(false,__('Invalid request'),);
        try {
            if(strpos($pair,'_')) {
                $ar =  explode('_',$pair);
                $tradeCoinId = get_coin_id_test(strtoupper($ar[0]));
                $baseCoinId = get_coin_id_test(strtoupper($ar[1]));
                if ($baseCoinId == 0 || $tradeCoinId == 0) {
                    return response()->json(responseData(false,__('Invalid symbol')));
                }

                if ($request->limit) {
                    $limit = $request->limit;
                } else {
                    $limit = 10;
                }
                $request->merge([
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                    'dashboard_type' => 'dashboard',
                    'per_page' => $limit
                ]);
                $data = $this->getMarketTransactiondata($request);

                if ($data) {
                    $response = responseData(true,__('success'),$data);
                } else {
                    $response = responseData(false,__('No data found'),$data);
                }
            } else {
                $response = responseData(false,__('The pair should be like -> BTC_USDT'),);
            }
        } catch (\Exception $e) {
            $response = responseData(false,__('Something went wrong'),);
        }
        return response()->json($response);
    }

    // get chart data
    public function getExchangeChart(Request $request,$pair)
    {
        $response = responseData(false,__('Invalid request'),);
        try {
            if(strpos($pair,'_')) {
                $ar =  explode('_',$pair);
                $tradeCoinId = get_coin_id_test(strtoupper($ar[0]));
                $baseCoinId = get_coin_id_test(strtoupper($ar[1]));
                if ($baseCoinId == 0 || $tradeCoinId == 0) {
                    return response()->json(responseData(false,__('Invalid symbol')));
                }

                $request->merge([
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                ]);
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
                        'message' => __('start time is always big than end time'),
                        'data' => []
                    ]);
                }
                $data = $chartService->getChartData($startTime, $endTime, $interval, $baseCoinId, $tradeCoinId);

                $response = responseData(true,__('Success'),$data);
            } else {
                $response = responseData(false,__('The pair should be like -> BTC_USDT'),);
            }
        } catch (\Exception $e) {
            $response = responseData(false,__('Something went wrong'),);
        }
        return response()->json($response);
    }



    // get order data
    public function getMarketTransactiondata($request)
    {
        $response['limit'] = $request->per_page;
        $service = new DashboardService();
        $data = $service->getMarketTransactions($request)['data']['transactions'];
        $response['trade'] = $data;

        return $response;
    }



    // get price data
    public function getMarketPriceDataPair()
    {
        $pairservice = new CoinPairService();
        $data = [];
        $items = $pairservice->getAllCoinPairs()['data'];

        if(isset($items[0])) {
            foreach ($items as $item) {
                $data[] = [
                    'symbol' => $item['coin_pair'],
                    'trade_coin' => $item['child_coin_name'],
                    'base_coin' => $item['parent_coin_name'],
                    'price' => $item['last_price'],
                    'price_change_24h' => $item['price_change'],
                    'volume_24h' => $item['volume'],
                    'high_24h' => $item['high'],
                    'low_24h' => $item['low'],
                ];
            }
        }
        return $data;
    }

    // get price data
    public function getMarketPriceDataSet($baseCoinId,$tradeCoinId)
    {
        $result = (object)[];
        $dashboardService = new DashboardService();
        $data = $dashboardService->getCoinPair($baseCoinId, $tradeCoinId);
        if ($data) {
            $result = [
                'symbol' => $data->child_coin_name.'_'.$data->parent_coin_name,
                'trade_coin' => $data->child_full_name,
                'base_coin' => $data->parent_full_name,
                'price' => $data->last_price,
                'price_change_24h' => $data->price_change,
                'volume_24h' => $data->volume,
                'high_24h' => $data->high,
                'low_24h' => $data->low,
            ];
        }
        return $result;
    }
}
