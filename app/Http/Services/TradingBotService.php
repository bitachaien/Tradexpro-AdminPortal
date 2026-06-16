<?php

namespace App\Http\Services;

use App\Jobs\SinglePairBotOrder;
use App\Model\CoinPair;
use App\Model\CoinPairApiPrice;
use Illuminate\Support\Facades\DB;

class TradingBotService
{

    public function placeBotOrder($userId)
    {
        try {
            $coinPairs = CoinPair::select(
                'coin_pairs.id',
                'parent_coin_id as base_coin_id',
                'child_coin_id as trade_coin_id',
                'coin_pairs.is_token',
                'coin_pairs.bot_trading',
                'coin_pairs.initial_price',
                'coin_pairs.bot_possible',
                'coin_pairs.bot_operation',
                'coin_pairs.bot_percentage',
                'coin_pairs.upper_threshold',
                'coin_pairs.lower_threshold',
                DB::raw("visualNumberFormat(price) as last_price"),
                'child_coin.coin_type as trade_coin_type',
                'parent_coin.coin_type as base_coin_type',
                'child_coin.coin_price as trade_coin_usd_rate',
                'parent_coin.coin_price as base_coin_usd_rate',
                DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
            )
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE, 'coin_pairs.bot_trading' => STATUS_ACTIVE])
                ->orderBy('coin_pairs.id', 'asc')
                ->get();
            // dd($coinPairs);
            if (isset($coinPairs[0])) {
                foreach ($coinPairs as $pair) {
                    // updateAdminWalletBalance($user->id,$pair->base_coin_id,$pair->trade_coin_id,$pair->base_coin_type,$pair->trade_coin_type);
                    // storeBotException('pair loop pair start  => '.$pair->pair_bin.' ', date('Y-m-d H:i:s'));
                    $this->processSinglePairBotOrder($userId, $pair);
                    // dispatch(new SinglePairBotOrder($userId,$pair))->onQueue('market-bot');
                    // storeBotException('pair loop pair end  => '.$pair->pair_bin.' ', date('Y-m-d H:i:s'));

                }
            }
        } catch (\Exception $e) {
            storeException('placeBotOrder', $e->getMessage());
        }
    }

    // process single pair order
    public function processSinglePairBotOrder($user, $pair)
    {
        storeBotException('processSinglePairBotOrder', 'start');
        try {
            $service = new TradingBotOrderPlaceService();

            // if ($pair->is_token == STATUS_ACTIVE) {
            //     storeBotException('processSinglePairBotOrder token', $pair->pair_bin);
            //     $orderData = $this->generateBotOrderPriceAndAmount($pair);
            //     storeBotException('pair order data',$orderData);
            //     if (
            //         $orderData &&
            //         (($orderData['order_1']['price'] && $orderData['order_1']['amount']) ||
            //         ($orderData['order_2']['price'] && $orderData['order_2']['amount']))
            //     ) {

            //         $placeOrder = $service->placeBotBuySellOrder($orderData,$pair,$user);
            //         // dd('$orderData found', $orderData);
            //     }
            // } else {
            if ($apiPrice = CoinPairApiPrice::select('buy_price', 'buy_amount', 'sell_price', 'sell_amount')->where("pair", $pair->pair_bin)->first()) {
                storeBotException('ask data apiPrice', json_encode($apiPrice));
                $service->createMarketBuyOrder($pair, $apiPrice, $user);
                $service->createMarketSellOrder($pair, $apiPrice, $user);
            } else {
                storeBotException('processSinglePairBotOrder token else', $pair->pair_bin);
                // if ($pair->is_token == STATUS_ACTIVE){
                $orderData = $this->generateBotOrderPriceAndAmount($pair);
                storeBotException('pair order data else', $orderData);
                if (
                    $orderData &&
                    (($orderData['order_1']['price'] && $orderData['order_1']['amount']) ||
                        ($orderData['order_2']['price'] && $orderData['order_2']['amount']))
                ) {

                    $service->placeBotBuySellOrder($orderData, $pair, $user);
                }
                // }
            }
            // }


            // storeBotException('processSinglePairBotOrder', 'executed');
            // storeBotException('processSinglePairBotOrder', 'end');
        } catch (\Exception $e) {
            storeException('processSinglePairOrder ' . $pair->pair_bin, $e->getMessage());
        }
    }


    // generate order price and ammount
    public function generateBotOrderPriceAndAmount($pair)
    {
        // storeBotException('generateBotOrderPriceAndAmount', 'start');
        // $order_1 = $this->generateBotCustomPrice($pair,TRADE_TYPE_BUY);
        // $order_2 = $this->generateBotCustomPrice($pair,TRADE_TYPE_SELL);

        $order_1 = $this->generateBotCustomPriceNew($pair, TRADE_TYPE_BUY);
        $order_2 = $this->generateBotCustomPriceNew($pair, TRADE_TYPE_SELL);

        // storeBotException('generateBotOrderPriceAndAmount', 'end');
        $response = [
            'order_1' => $order_1,
            'order_2' => $order_2
        ];

        return $response;
    }

    public function getBotPrice($operation, $marketPrice, $randomPercentFactor)
    {
        $price = 0;
        $priceChange = bcmulx($marketPrice, bcdivx($randomPercentFactor, 100, 8), 8);
        if ($operation == BOT_INCREASE_MARKET_PRICE) {
            $price = bcaddx($marketPrice, $priceChange, 8);
        } elseif ($operation == BOT_DECREASE_MARKET_PRICE) {
            $price = bcsubx($marketPrice, $priceChange);
        }

        return $price;
    }

    // calculate price new
    public function calculatePriceNew($pair, $randomPercentFactor, $marketPrice = null, $operation = BOT_RANDOM_MARKET_PRICE)
    {
        $price = 0;
        $priceChange = 0;
        if (!$marketPrice) {
            $marketPrice = CoinPair::find($pair->id)->price;
        }
        if ($operation == BOT_RANDOM_MARKET_PRICE) {
            $operation = getRandOperation();
        }
        $maxPrice = $pair->upper_threshold;
        $minPrice = $pair->upper_threshold;
        $priceMatch = 1;
        storeBotException('calculatePrice rand operation => ', $operation);
        if ($operation == BOT_SYNC_MARKET_PRICE) {
            $response = $this->processOperationNeutral($pair);
            $price = $response['price'];
            $priceChange = $response['priceChange'];
        } else {
            $price = $this->getBotPrice($operation, $marketPrice, $randomPercentFactor);
        }

        $res = [
            'price' => $price,
            'marketPrice' => $marketPrice,
            'priceChange' => $priceChange,
            'operation' => $operation,
        ];
        storeBotException('calculatePrice', $res);
        return $res;
    }

    // generate custom price
    public function generateBotCustomPriceNew(
        $pair,
        $tradeType,
        $operation = BOT_RANDOM_MARKET_PRICE,
        $percentLeftLen = 0,
        $percentDecimal = 0,
        $maxPercent = 0,
        $minPercent = 0
    ) {
        // storeBotException('generateBotCustomPrice', 'start');
        try {
            $randomPercentFactor = $pair->bot_percentage;
            $price = 0;
            // $coinPairDecimal = $pair->pair_decimal;
            $coinPairDecimal = 8;
            $operation = $pair->bot_operation;

            if ($pair->last_price >= $pair->upper_threshold) {
                $operation = BOT_DECREASE_MARKET_PRICE;
                // CoinPair::where('id',$pair->id)->update(['bot_operation' => BOT_DECREASE_MARKET_PRICE]);
            }
            if ($pair->last_price <= $pair->lower_threshold) {
                $operation == BOT_INCREASE_MARKET_PRICE;
                // CoinPair::where('id',$pair->id)->update(['bot_operation' => BOT_INCREASE_MARKET_PRICE]);
            }

            if ($operation == BOT_RANDOM_MARKET_PRICE) {
                $operation = getRandOperation();
            }

            storeBotException('generateBotCustomPrice operation => ', $operation);
            storeBotException('tradeType => ', $tradeType);
            $amount = $this->generateCustomBotAmount($pair);
            storeBotException('generateCustomBotAmount => ', $amount);
            if ($amount > 0) {
                if (($tradeType == TRADE_TYPE_BUY && $operation == BOT_INCREASE_MARKET_PRICE) ||
                    ($tradeType == TRADE_TYPE_SELL && $operation == BOT_DECREASE_MARKET_PRICE)
                ) {

                    storeBotException('generateLessDiffPricePercentFactor => ', 'condition match');
                    if ($randomPercentFactor > 0) {
                        $randomPercentFactor = $randomPercentFactor;
                    } else {
                        $priceFactorRes = $this->generateLessDiffPricePercentFactor($pair);
                        $randomPercentFactor = $priceFactorRes['percentFactor'];
                    }


                    $response = $this->calculatePriceNew($pair, $randomPercentFactor, null, $operation);
                    storeBotException('generateCustomBotAmount generateLessDiffPricePercentFactor => ', $response);
                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                } else {
                    if ($operation == BOT_RANDOM_MARKET_PRICE) {
                        $randomPercentFactor = $this->generateRandPricePercentFactor(
                            $percentLeftLen,
                            $percentDecimal,
                            $maxPercent,
                            $minPercent
                        );

                        storeBotException(' else randomPercentFactor ', $randomPercentFactor);
                    }
                    $response = $this->calculatePriceNew($pair, $randomPercentFactor, null, $operation);
                    storeBotException('generateBotCustomPrice calculatePrice  response => ', $response);

                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                }

                $price = formatAmountDecimal($price, $coinPairDecimal);
                storeBotException(' formatAmountDecimal price => ', $price);
                if ($operation == BOT_SYNC_MARKET_PRICE) {
                    $curr_market_price = CoinPair::find($pair->id)->price;

                    if ($curr_market_price > $price) {
                        $tradeType = TRADE_TYPE_SELL;
                    } elseif ($curr_market_price < $price) {
                        $tradeType = TRADE_TYPE_BUY;
                    }

                    if ($curr_market_price != $price) {
                        storeBotException(' $curr_market_price price => ', $curr_market_price);
                        storeBotException(' $price price => ', $price);
                        $amount += bcmulx($amount, bcdivx(1000, 100, 8), 8);
                        storeBotException(' $price amount => ', $amount);
                    }
                }
                $amount = formatAmountDecimal($amount, $coinPairDecimal);

                // storeBotException('coin pair => ',$pair->pair_bin);
                // storeBotException('order type => ',$tradeType);
                // storeBotException('market price => ',$marketPrice);
                // storeBotException('randomPercentFactor => ',$randomPercentFactor);
                // storeBotException('operation => ',$operation);
                // storeBotException('priceChange => ',$priceChange);
                // storeBotException('price => ',$price);
                // storeBotException('amount => ',$amount);
            }
            if ($pair->upper_threshold > 0 && $pair->lower_threshold > 0) {
                storeBotException('get calculated Price => ', $price);
                if ($price <= $pair->lower_threshold) {
                    $price = $pair->lower_threshold;
                    storeBotException('tradeType lower_threshold =>', $tradeType);
                    storeBotException('price =>', $price);
                }
                if ($price >= $pair->upper_threshold) {
                    $price = $pair->upper_threshold;
                    storeBotException('tradeType upper_threshold =>', $tradeType);
                    storeBotException('price =>', $price);
                }
            }
            $resData = [
                'price' => $price,
                'amount' => $amount,
                'orderType' => $tradeType
            ];


            return $resData;
        } catch (\Exception $e) {
            storeException('generateBotCustomPrice ' . $pair->pair_bin, $e->getMessage());
        }
    }

    // generate custom price
    public function generateBotCustomPrice(
        $pair,
        $tradeType,
        $operation = BOT_RANDOM_MARKET_PRICE,
        $percentLeftLen = 0,
        $percentDecimal = 0,
        $maxPercent = 0,
        $minPercent = 0
    ) {
        // storeBotException('generateBotCustomPrice', 'start');
        try {
            $randomPercentFactor = 0;
            $price = 0;
            $priceChange = 0;
            $marketPrice = 0;
            $coinPairDecimal = 8;

            if ($operation == BOT_RANDOM_MARKET_PRICE) {
                $operation = getRandOperation();
            }
            storeBotException('generateBotCustomPrice operation => ', $operation);
            storeBotException('tradeType => ', $tradeType);
            $amount = $this->generateCustomBotAmount($pair);
            storeBotException('generateCustomBotAmount => ', $amount);
            if ($amount > 0) {
                if (($tradeType == TRADE_TYPE_BUY && $operation == BOT_INCREASE_MARKET_PRICE) ||
                    ($tradeType == TRADE_TYPE_SELL && $operation == BOT_DECREASE_MARKET_PRICE)
                ) {

                    storeBotException('generateLessDiffPricePercentFactor => ', 'condition match');
                    $priceFactorRes = $this->generateLessDiffPricePercentFactor($pair);

                    $randomPercentFactor = $priceFactorRes['percentFactor'];

                    $response = $this->calculatePrice($pair, $randomPercentFactor, null, $operation);
                    storeBotException('generateCustomBotAmount generateLessDiffPricePercentFactor => ', $response);
                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                } else {
                    if ($operation != BOT_SYNC_MARKET_PRICE) {
                        $randomPercentFactor = $this->generateRandPricePercentFactor(
                            $percentLeftLen,
                            $percentDecimal,
                            $maxPercent,
                            $minPercent
                        );
                    }
                    $response = $this->calculatePrice($pair, $randomPercentFactor, null, $operation);
                    storeBotException('generateBotCustomPrice calculatePrice  response => ', $response);

                    $price = $response['price'];
                    $operation = $response['operation'];
                    $priceChange = $response['priceChange'];
                    $marketPrice = $response['marketPrice'];
                }

                $price = formatAmountDecimal($price, $coinPairDecimal);
                storeBotException(' formatAmountDecimal price => ', $price);
                if ($operation == BOT_SYNC_MARKET_PRICE) {
                    $curr_market_price = CoinPair::find($pair->id)->price;

                    if ($curr_market_price > $price) {
                        $tradeType = TRADE_TYPE_SELL;
                    } elseif ($curr_market_price < $price) {
                        $tradeType = TRADE_TYPE_BUY;
                    }

                    if ($curr_market_price != $price) {
                        storeBotException(' $curr_market_price price => ', $curr_market_price);
                        storeBotException(' $price price => ', $price);
                        $amount += bcmulx($amount, bcdivx(1000, 100, 8), 8);
                        storeBotException(' $price amount => ', $amount);
                    }
                }
                $amount = formatAmountDecimal($amount, $coinPairDecimal);

                // storeBotException('coin pair => ',$pair->pair_bin);
                // storeBotException('order type => ',$tradeType);
                // storeBotException('market price => ',$marketPrice);
                // storeBotException('randomPercentFactor => ',$randomPercentFactor);
                // storeBotException('operation => ',$operation);
                // storeBotException('priceChange => ',$priceChange);
                // storeBotException('price => ',$price);
                // storeBotException('amount => ',$amount);
            }
            $resData = [
                'price' => $price,
                'amount' => $amount,
                'orderType' => $tradeType
            ];

            return $resData;
        } catch (\Exception $e) {
            storeException('generateBotCustomPrice ' . $pair->id, $e->getMessage());
        }
    }

    // generate bot custom ammount
    public function generateCustomBotAmount($pair)
    {
        $result = 0;
        try {
            // storeBotException('generateCustomBotAmount trade_coin_type => ',$pair->trade_coin_type);
            $usdPrice = getConvertAmount($pair, $pair->trade_coin_type, 'USD', 1);
            // storeBotException('generateCustomBotAmount usdPrice => ',$usdPrice);
            $dividendFactor = getRandomIntFromRange(1, 100);
            // storeBotException('generateCustomBotAmount dividendFactor => ',$dividendFactor);
            $result = bcdivx($dividendFactor, $usdPrice, 8);
            // storeBotException('generateCustomBotAmount result => ',$result);
        } catch (\Exception $e) {
            storeException('generateCustomBotAmount ' . $pair->id, $e->getMessage());
        }
        return $result;
    }

    // generate less different price percent factor
    public function generateLessDiffPricePercentFactor($pair, $price = null, $randDiff = null, $randStart = null, $randEnd = null)
    {
        if (!$price) {
            $price = CoinPair::find($pair->id)->price;
        }
        $percentFactor = 0;
        $leftLength = getLeftSideLength($price);
        if ($price >= 1) {
            if ($leftLength >= 3) {
                $randStart = $randStart ? $randStart : generateNumOfZeros($leftLength - 3);
                $maxRandEnd = generateNumOfZeros($leftLength - 2);
                $wouldBeRandEnd = null;
                if ($randDiff) {
                    $wouldBeRandEnd = bcaddx($randStart, $randDiff, 8);
                    if ($wouldBeRandEnd > $maxRandEnd) {
                        $randEnd = $maxRandEnd;
                    }
                }
                $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;
                $dividendFactor = getRandomIntFromRange($randStart, $randEnd);
                $percentFactor = bcdivx($dividendFactor, $price);
            } else {
                $randStart = $randStart ?? generateNumOfZeros($leftLength - 1);

                $maxRandEnd = generateNumOfZeros($leftLength);
                $wouldBeRandEnd = null;
                if ($randDiff) {
                    $wouldBeRandEnd = bcaddx($randStart, $randDiff, 8);
                    if ($wouldBeRandEnd > $maxRandEnd) $randEnd = $maxRandEnd;
                }
                $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;

                $dividendFactor = getRandomIntFromRange($randStart, $randEnd);
                $percentFactor = bcdivx($dividendFactor, $price, 8);
            }
        } else {
            $len_zeros = getConsecutiveZeroLength($price);
            $randStart = $randStart ?? generateNumOfZeros($len_zeros);

            $maxRandEnd = generateNumOfZeros($len_zeros + 1);
            $wouldBeRandEnd = null;
            if ($randDiff) {
                $wouldBeRandEnd = bcaddx($randStart, $randDiff);
                if ($wouldBeRandEnd > $maxRandEnd) $randEnd = $maxRandEnd;
            }

            $randEnd = $randEnd ?? $wouldBeRandEnd ?? $maxRandEnd;

            $multiplierFactor = getRandomIntFromRange($randStart, $randEnd);
            $percentFactor = bcmulx($multiplierFactor, $price, 8);
        }
        $data = [
            'percentFactor' => $percentFactor,
            'price' => $price
        ];
        storeBotException('generateLessDiffPricePercentFactor data  => ', $data);
        return $data;
    }

    // calculate price
    public function calculatePrice($pair, $randomPercentFactor, $marketPrice = null, $operation = BOT_RANDOM_MARKET_PRICE)
    {
        $price = 0;
        $priceChange = 0;
        if (!$marketPrice) {
            $marketPrice = CoinPair::find($pair->id)->price;
        }
        if ($operation == BOT_RANDOM_MARKET_PRICE) {
            $operation = getRandOperation();
        }
        storeBotException('calculatePrice rand operation => ', $operation);
        if ($operation == BOT_SYNC_MARKET_PRICE) {
            $response = $this->processOperationNeutral($pair);
            $price = $response['price'];
            $priceChange = $response['priceChange'];
        } else {

            $priceChange = bcmulx($marketPrice, bcdivx($randomPercentFactor, 100, 8), 8);
            if ($operation == BOT_INCREASE_MARKET_PRICE) {
                $price = bcaddx($marketPrice, $priceChange, 8);
            } elseif ($operation == BOT_DECREASE_MARKET_PRICE) {
                $price = bcsubx($marketPrice, $priceChange);
            }
        }

        $res = [
            'price' => $price,
            'marketPrice' => $marketPrice,
            'priceChange' => $priceChange,
            'operation' => $operation,
        ];
        storeBotException('calculatePrice', $res);
        return $res;
    }

    // process operation neutral
    public function processOperationNeutral($pair)
    {
        // here trying to generate a price, which will be much close to current price from global market
        $data = $this->generatePriceUsingGlobalPrice($pair, BOT_SYNC_MARKET_PRICE, RATE_SOURCE_EXTERNAL);
        $res = [
            'price' => $data['price'],
            'priceChange' => $data['priceChange']
        ];
        return $res;
    }

    // generate price using global price
    public function generatePriceUsingGlobalPrice(
        $pair,
        $operation = BOT_RANDOM_MARKET_PRICE,
        $source = RATE_SOURCE_DB,
        $marketPrice = null
    ) {
        if (!$marketPrice) {
            $marketPrice = CoinPair::find($pair->id)->price;
        }
        if ($operation == BOT_RANDOM_MARKET_PRICE) {
            $operation = getRandOperation();
        }
        $price = 0;
        $priceChange = 0;

        $globalPrice = getConvertAmount($pair, $pair->trade_coin_type, $pair->base_coin_type, 1, $source);
        if ($operation == BOT_SYNC_MARKET_PRICE) {
            $price = $globalPrice;
        } else {
            $percentFactor = $this->generateLessDiffPricePercentFactor($pair, $globalPrice, 1000);
            $change = bcmulx($globalPrice, bcdivx($percentFactor['percentFactor'], 100, 8), 8);
            if ($operation == BOT_INCREASE_MARKET_PRICE) {
                $price = bcaddx($globalPrice, $change, 8);
            } elseif ($operation == BOT_DECREASE_MARKET_PRICE) {
                $price = bcsubx($globalPrice, $change);
            }
        }

        $priceChange = $priceChange = abs($price - $marketPrice);

        $res = [
            'price' => $price,
            'marketPrice' => $marketPrice,
            'priceChange' => $priceChange,
            'operation' => $operation,
        ];

        storeBotException('generatePriceUsingGlobalPrice', $res);
        return $res;
    }

    public function generateRandPricePercentFactor(
        $percent_left_len,
        $percent_decimal,
        $max_percent,
        $min_percent
    ) {
        $percent_left_len = $percent_left_len
            ? $percent_left_len
            : PERCENT_LEFT_LEN;
        $percent_decimal = $percent_decimal
            ? $percent_decimal
            : PERCENT_DECIMAL;
        $max_percent = $max_percent
            ? $max_percent
            : MAX_PERCENT;
        $min_percent = $min_percent
            ? $min_percent
            : MIN_PERCENT;

        $randomPercentFactor =
            getRandomDecimalNumber($percent_left_len, $percent_decimal) ||
            $min_percent;
        if ($randomPercentFactor > $max_percent) {
            $randomPercentFactor = getRandomIntFromRange(1, 5) || 3;
        }

        storeBotException('generateRandPricePercentFactor => ', $randomPercentFactor);
        return $randomPercentFactor;
    }

    public function getCoinPairPriceFromApi()
    {
        try {
            $coinPairs = CoinPair::select(
                'coin_pairs.id',
                'parent_coin_id as base_coin_id',
                'child_coin_id as trade_coin_id',
                'coin_pairs.bot_trading',
                DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin')
            )
                ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
                ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
                ->where(['coin_pairs.status' => STATUS_ACTIVE, 'coin_pairs.bot_trading' => STATUS_ACTIVE])
                ->orderBy('coin_pairs.id', 'asc')
                ->get();
            // dd($coinPairs);
            if (isset($coinPairs[0])) {
                foreach ($coinPairs as $pair) {
                    $callApi = getPriceFromApi($pair->pair_bin);
                    if (isset($callApi['success']) && $callApi['success']) {
                        $data = $callApi['data']  ?? [];
                        $sell = $data['sellData'] ?? [];
                        $buy  = $data['buyData']  ?? [];

                        CoinPairApiPrice::updateOrCreate([
                            "pair" => $pair->pair_bin
                        ], [
                            "buy_price"   => $buy[0][0],
                            "buy_amount"  => $buy[0][1],
                            "sell_price"  => $sell[0][0],
                            "sell_amount" => $sell[0][1],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            storeBotException('getCoinPairPriceFromApi', $e->getMessage());
        }
    }
}
