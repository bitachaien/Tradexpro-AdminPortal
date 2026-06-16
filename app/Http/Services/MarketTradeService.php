<?php

namespace App\Http\Services;

use App\Http\Repositories\CoinPairRepository;
use App\Model\CoinPair;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarketTradeService
{
    private $settingTolerance;
    function __construct()
    {
        $this->settingTolerance = settings('trading_bot_price_tolerance') ?? 10;
    }
    // market buy order
    public function makeMarketOrder($request, $coinPair = null, $marketData = null)
    {
        try {
            if (allsetting('enable_bot_trade') == STATUS_ACTIVE) {
                $request->merge(['is_market' => 0]);
                if (isset($request->pair_id) && !is_null($coinPair)) {
                    if ($coinPair) {
                        if ($coinPair['is_token'] == 1) {
                            $this->makeMarketOrderToken($request, $coinPair, $marketData);
                        } else {
                            $price = number_format($marketData[0][0], 8, '.', '');
                            $amount = $marketData[0][1];
                            //                            $startDate = Carbon::now()->subMinutes(10)->startOfMinute();
                            //                            $endDate = Carbon::now();
                            ////                        $dataMarket = json_decode($marketData,true);
                            //                            $dataMarket = ($marketData);
                            //                            $orders = [];
                            //                            $orderSlice = random_int(100, 200);
                            //                            $count = 0;
                            //                            while ($startDate <= $endDate) {
                            //                                $price = number_format($dataMarket[$count][0], 8, '.', '');
                            //                                $amount = $dataMarket[$count][1];
                            //                                $intervalDate = Carbon::parse(intval($startDate->unix() / 300) * 300);
                            //                                if ($startDate->equalTo($intervalDate) && count($orders) > 0) {
                            //                                    $price = end($orders)['price'];
                            //                                }
                            //                                $orders[] = [
                            //                                    'type' => $request->bot_order_type,
                            //                                    'price' => $price,
                            //                                    'amount' => $amount,
                            //                                ];
                            $request->merge(['price' => $price]);
                            $request->merge(['amount' => $amount]);
                            if ($request->bot_order_type == 'buy') {
                                $this->startBotBuyOrderProcess($request, $coinPair);
                            } elseif ($request->bot_order_type == 'sell') {
                                $this->startBotSellOrderProcess($request, $coinPair);
                            }

                            //                                $orderSlice--;
                            //                                if ($orderSlice === 0) {
                            //                                    $orderSlice = random_int(100, 200);
                            //                                }
                            //                                $startDate->addMinutes(random_int(0, 30));
                            //                                $count++;
                            //                            }
                        }
                    } else {
                        storeException('marketBuyOrder', 'this coin pair not found or not active now');
                    }
                }
                return true;
            }
        } catch (\Exception $e) {
            storeException('marketBuyOrder ex', $e->getMessage());
        }
    }

    // bot trading for token coin pair
    public function makeMarketOrderToken($request, $coinPair, $marketData)
    {
        try {
            $startDate = Carbon::now()->subMinutes(10)->startOfMinute();
            $endDate = Carbon::now();
            $dataMarket = ($marketData);
            $orders = [];
            $orderSlice = random_int(100, 200);
            $count = 0;
            while ($startDate <= $endDate) {
                if (isset($dataMarket[$count])) {
                    $price = number_format($dataMarket[$count]['price'], 8, '.', '');
                } else {
                    $price =  $coinPair['last_price'];
                }
                if ($request->bot_order_type == 'buy') {
                    $price = $this->getPrice($price, 'small');
                } else {
                    $price = $this->getPrice($price, 'big');
                }

                $amount = $this->getAmount($price);
                $intervalDate = Carbon::parse(intval($startDate->unix() / 300) * 300);
                if ($startDate->equalTo($intervalDate) && count($orders) > 0) {
                    $price = end($orders)['price'];
                }
                $orders[] = [
                    'type' => $request->bot_order_type,
                    'price' => $price,
                    'amount' => $amount,
                ];
                $request->merge(['price' => $price]);
                $request->merge(['amount' => $amount]);

                if ($request->bot_order_type == 'buy') {
                    $this->startBotBuyOrderProcess($request, $coinPair);
                } elseif ($request->bot_order_type == 'sell') {
                    $this->startBotSellOrderProcess($request, $coinPair);
                }

                $orderSlice--;
                if ($orderSlice === 0) {
                    $orderSlice = random_int(100, 200);
                }
                $startDate->addMinutes(random_int(0, 30));
                $count++;
            }
        } catch (\Exception $e) {
            storeException('makeMarketOrderToken', $e->getMessage());
        }
    }
    // get token price
    public function getPrice($lastPrice, $type)
    {
        $div = pow(10, 8);
        $settingTolerance = 1;
        $tolerancePrice = bcdivx(bcmulx($lastPrice, $settingTolerance), "100");
        $highTolerance = bcaddx($lastPrice, $tolerancePrice);
        $lowTolerance = bcsubx($lastPrice, $tolerancePrice);
        $tPrice = custom_number_format(rand($lowTolerance * $div, $highTolerance * $div) / $div);

        if ($type == 'big') {
            $tolerance = bcaddx($tPrice, $tolerancePrice);
            $price = custom_number_format(rand($lastPrice * $div, $tolerance * $div) / $div);
        } else {
            $tolerance = bcsubx($tPrice, $tolerancePrice);
            $price = custom_number_format(rand($tolerance * $div, $lastPrice * $div) / $div);
        }
        $newPrice = custom_number_format(rand($tPrice * $div, $price * $div) / $div);

        return $newPrice;
    }

    public function getAmount($price)
    {
        $div = pow(10, 8);
        if ($price >= 1) {
            $amount = custom_number_format(rand(0.00001 * $div, 0.1 * $div) / $div);
        } else {
            $amount = custom_number_format(rand(0.1 * $div, 3 * $div) / $div);
        }
        return $amount;
    }
    // start bot buy order
    public function startBotBuyOrderProcess($request, $pair)
    {
        $request->merge(['bot_order_type' => 'buy']);
        $request->merge([
            'base_coin_id' => $pair['parent_coin_id'],
            'trade_coin_id' => $pair['child_coin_id'],
            'is_bot' => STATUS_ACTIVE
        ]);
        $this->placeBuyOrderByBot($request);
    }
    // start bot sell order
    public function startBotSellOrderProcess($request, $pair)
    {
        $request->merge(['bot_order_type' => 'sell']);
        $request->merge([
            'base_coin_id' => $pair['parent_coin_id'],
            'trade_coin_id' => $pair['child_coin_id'],
            'is_bot' => STATUS_ACTIVE
        ]);
        $this->placeSellOrderByBot($request);
    }

    // make place order data
    public function makeOrderPlaceData($request, $baseCoinId, $tradeCoinId)
    {
        $data = [];
        try {
            $price = $this->getBuySellLatestPrice($baseCoinId, $tradeCoinId);
            if ($request->bot_order_type == 'buy') {
                $lastPrice = $price['buy_price'];
                $data['amount'] = $price['buy_amount'];
            } else {
                $lastPrice = $price['sell_price'];
                $data['amount'] = $price['sell_amount'];
            }
            storeBotException('order type ' . $request->bot_order_type, $lastPrice . ' and amount = ' . $data['amount']);
            $settingTolerance = floatval($this->settingTolerance);
            $tolerancePrice = bcdivx(bcmulx($lastPrice, $settingTolerance), "100");
            $highTolerance = bcaddx($lastPrice, $tolerancePrice);
            $lowTolerance = bcsubx($lastPrice, $tolerancePrice);
            storeBotException('$settingTolerance = ' . $settingTolerance . ' and $tolerancePrice price = ' . $tolerancePrice, '$highTolerance = ' . $highTolerance . ' and $lowTolerance = ' . $lowTolerance);

            $div = pow(10, 8);
            $data['requestPrice'] = custom_number_format(rand($lowTolerance * $div, $highTolerance * $div) / $div);

            return $data;
        } catch (\Exception $e) {
            storeException('makeOrderPlaceData ex', $e->getMessage());
            return $data;
        }
    }

    // unset some request
    public function unsetSomeRequest($request)
    {
        if (isset($request->maker_fees)) {
            unset($request['maker_fees']);
        }
        if (isset($request->taker_fees)) {
            unset($request['taker_fees']);
        }
        if (isset($request->btc_rate)) {
            unset($request['btc_rate']);
        }
        if (isset($request->dashboard_type)) {
            unset($request['dashboard_type']);
        }
        if (isset($request->order_type)) {
            unset($request['order_type']);
        }
    }
    // place buy order
    public function placeBuyOrderByBot($request)
    {
        $this->unsetSomeRequest($request);
//        storeException('placeBuyOrderByBot request', json_encode($request->all()));
        if($request->price > 0 && $request->amount > 0) {
            app(BuyOrderService::class)->botOrderCreate($request);
//            storeBotException('placeBuyOrderByBot response', json_encode($response));
        }
    }

    // place sell order
    public function placeSellOrderByBot($request)
    {
        $this->unsetSomeRequest($request);
//        storeException('placeSellOrderByBot request', json_encode($request->all()));
        if($request->price > 0 && $request->amount > 0) {
            app(SellOrderService::class)->botOrderCreate($request);
//            storeBotException('placeSellOrderByBot response', json_encode($response));
        }
    }

    // get buy sell last price
    public function getBuySellLatestPrice($baseCoinId, $tradeCoinId)
    {
        $data['sell_price'] = 0;
        $data['buy_price'] = 0;
        try {
            $dashboardService = new DashboardService();
            $coinPairRepo = new CoinPairRepository(CoinPair::class);
            $pairData = $coinPairRepo->getCoinPairsDataBot($baseCoinId, $tradeCoinId);
            $price = $dashboardService->getTotalVolumeBot($baseCoinId, $tradeCoinId);
            storeBotException("price sell_price", $price['sell_price']);
            storeBotException("price buy_price", $price['buy_price']);
            storeBotException("price pairData->last_price", $pairData->last_price);
            $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $pairData->last_price;
            $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $pairData->last_price;
            $amount = getBuySellLastAmount($data, $baseCoinId, $tradeCoinId);
            $data['buy_amount'] = $amount['buy_amount'];
            $data['sell_amount'] = $amount['sell_amount'];
        } catch (\Exception $e) {
            storeException('getBuySellLatestPrice', $e->getMessage());
        }

        return $data;
    }
}
