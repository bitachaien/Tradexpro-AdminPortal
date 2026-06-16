<?php

namespace App\Http\Services;


use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\ConditionBuyRepository;
use App\Http\Repositories\ConditionSellRepository;
use App\Http\Repositories\ConditionStopLimitRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Jobs\ConditionBuyOrderProcessJob;
use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\ConditionBuy;
use App\Model\ConditionSell;
use App\Model\ConditionStopLimit;
use App\Model\Sell;
use App\Model\StopLimit;
use App\Model\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConditionalBuyOrderService extends BaseService
{
    public $model = ConditionBuy::class;

    public $repository = ConditionBuyRepository::class;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }
    /**
     * Place condition buy
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $coinPairsService = new CoinPairService();
        $coinPairs = $coinPairsService->getDocs(['parent_coin_id' => $request->base_coin_id, 'child_coin_id' => $request->trade_coin_id]);
        if (empty($coinPairs)) {
            return [
                'status' => false,
                'message' => 'Invalid order request!',
            ];
        }
        if ((!empty($request['stop_price']) && !empty($request['stop_limit'])) && ($request['stop_limit'] >= $request['stop_price'])) {
            return [
                'status' => false,
                'message' => __('Stop value must be greater than limit value for sell stop limit')
            ];
        }
        try {
            $response = false;
            DBService::beginTransaction();

            $temporaryFees = calculated_fee_limit(Auth::id());
            /*$request->request->add([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => $request['buy_price']//custom_number_format($btcRate)
            ]);*/

            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => $request['buy_price'] //custom_number_format($btcRate)
            ]);

            // extra checking
            $totalBuyAmount = $request['buy_amount'];
            $sellTotal1 = 0;
            $sellTotal2 = 0;
            $sellTotal3 = 0;

            $checkSell1 = 1;
            $checkSell2 = 1;
            $checkSell3 = 1;

            // check if advanced sell orders empty or not
            if (empty($request['sell_price_1']) || empty($request['sell_amount_1'])) {
                $checkSell1 = 0;
            }

            if (empty($request['sell_price_2']) || empty($request['sell_amount_2'])) {
                $checkSell2 = 0;
            }

            if (empty($request['sell_price_3']) || empty($request['sell_amount_3'])) {
                $checkSell3 = 0;
            }

            if ($checkSell1 == 0 && $checkSell2 == 0 && $checkSell3 == 0) {
                DBService::rollBack();
                return response()->json(['status' => false, 'message' => __('You need to place minimum one sell order')]);
            }

            // checking if sell orders exist or not
            if (
                (isset($request['sell_price_1']) && !empty($request['sell_price_1']) && isset($request['sell_amount_1']) && !empty($request['sell_amount_1'])) ||

                (isset($request['sell_price_2']) && !empty($request['sell_price_2']) && isset($request['sell_amount_2']) && !empty($request['sell_amount_2'])) ||

                (isset($request['sell_price_3']) && !empty($request['sell_price_3']) && isset($request['sell_amount_3']) && !empty($request['sell_amount_3']))
            ) {
                if (isset($request['sell_price_1']) && !empty($request['sell_price_1']) && isset($request['sell_amount_1']) && !empty($request['sell_amount_1'])) {
                    $sellTotal1 = bcaddx($sellTotal1, $request['sell_amount_1']);
                }

                if (isset($request['sell_price_2']) && !empty($request['sell_price_2']) && isset($request['sell_amount_2']) && !empty($request['sell_amount_2'])) {
                    $sellTotal2 = bcaddx($sellTotal2, $request['sell_amount_2']);
                }

                if (isset($request['sell_price_3']) && !empty($request['sell_price_3']) && isset($request['sell_amount_3']) && !empty($request['sell_amount_3'])) {
                    $sellTotal3 = bcaddx($sellTotal3, $request['sell_amount_3']);
                }

                $totalSellAmount = bcaddx($sellTotal1, bcaddx($sellTotal2, $sellTotal3));

                if (bccompx($totalBuyAmount, $totalSellAmount) !== 0) {
                    DBService::rollBack();
                    return [
                        'status' => false,
                        'message' => __('Mismatch in Buy order and sell orders amounts!')
                    ];
                }
            }


            $feesPercent = $request['maker_fees'] > $request['taker_fees'] ? $request['maker_fees'] : $request['taker_fees'];
            //            $walletDetails = json_decode(getService(['method' => 'getBalance', 'params' => ['user_id' => Auth::id(), 'coin_id' => $request->base_coin_id]]));
            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance(Auth::id(), $request->base_coin_id);
            $mainBalance = $walletDetails->balance;
            $totalAmount = bcmulx($request['buy_price'], $request['buy_amount']);
            $totalBuyCost = bcaddx($totalAmount, bcdivx(bcmulx($totalAmount, $feesPercent), "100"));
            $btcRate = getBtcRate($request->trade_coin_id);
            $request['btcrate'] = $btcRate;
            $request['btcrate1'] = $btcRate;
            $request['btcrate2'] = $btcRate;
            $request['btcrate3'] = $btcRate;
            $msg1 = $msg2 = $msg3 = $msg4 = "";
            $feesZero1 = $feesZero2 = $feesZero3 = 0;
            if (bccompx($mainBalance, $totalBuyCost) !== -1) {
                $feesZero = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->buy_amount, 'buy', custom_number_format($request->buy_price));
                if ($feesZero) {
                    DBService::rollBack();
                    return [
                        'status' => false,
                        'message' => __('Minimum Buy Total Should Be ') . $feesZero
                    ];
                }
                $request['user_id'] = Auth::id();
                // need to ready the insert value
                $buyData = [
                    'user_id' => $request['user_id'],
                    'trade_coin_id' => $request['trade_coin_id'],
                    'base_coin_id' => $request['base_coin_id'],
                    'amount' => $request['buy_amount'],
                    'price' => $request['buy_price'],
                    'btc_rate' => $request['btcrate'],
                    'maker_fees' => $request['maker_fees'],
                    'taker_fees' => $request['taker_fees'],
                    'category' => $request->get('category', 1),
                    'status' => $request->get('status', 0),
                ];
                $buy = $this->object->create($buyData);
                storeBotException("ConditionBuyOrderPlace", "Condition Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");
                //Deduct Amount from Main Balance
                //                $response = getService(['method' => 'deductBalanceById', 'params' => ['user_id' => $request['user_id'], 'coin_id' => $request->base_coin_id, 'amount' => $totalBuyCost]]);
                $response = $walletRepository->deductBalanceById($walletDetails, $totalBuyCost);
                if ($response == false) {
                    DBService::rollBack();
                    return [
                        'status' => false,
                        'message' => __('Failed to place conditional buy order.'),
                    ];
                }

                $dataNow = Carbon::now();
                if (isset($request['sell_price_1']) && !empty($request['sell_price_1']) && isset($request['sell_amount_1']) && !empty($request['sell_amount_1'])) {
                    $feesZero1 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->sell_amount_1, 'sell', $request->sell_price_1);
                    if ($feesZero1) {
                        $msg1 = __("Sell Total (" . bcmulx($request->sell_price_1, $request->sell_amount_1) . ")  Should Not Less Than ") . $feesZero1;
                    }
                    $conditionSellOrders[] = [
                        'user_id' => $request['user_id'],
                        'condition_buy_id' => $buy->id,
                        'trade_coin_id' => $request['trade_coin_id'],
                        'base_coin_id' => $request['base_coin_id'],
                        'amount' => $request['sell_amount_1'],
                        'price' => $request['sell_price_1'],
                        'btc_rate' => $request['btcrate1'],
                        'maker_fees' => $request['maker_fees'],
                        'taker_fees' => $request['taker_fees'],
                        'created_at' => $dataNow,
                        'updated_at' => $dataNow
                    ];
                }

                if (isset($request['sell_price_2']) && !empty($request['sell_price_2']) && isset($request['sell_amount_2']) && !empty($request['sell_amount_2'])) {
                    $feesZero2 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->sell_amount_2, 'sell', $request->sell_price_2);
                    if ($feesZero2) {
                        $msg2 = __("Sell Total (" . bcmulx($request->sell_price_2, $request->sell_amount_2) . ")  Should Not Less Than ") . $feesZero2;
                    }
                    $conditionSellOrders[] = [
                        'user_id' => $request['user_id'],
                        'condition_buy_id' => $buy->id,
                        'trade_coin_id' => $request['trade_coin_id'],
                        'base_coin_id' => $request['base_coin_id'],
                        'amount' => $request['sell_amount_2'],
                        'price' => $request['sell_price_2'],
                        'btc_rate' => $request['btcrate2'],
                        'maker_fees' => $request['maker_fees'],
                        'taker_fees' => $request['taker_fees'],
                        'created_at' => $dataNow,
                        'updated_at' => $dataNow
                    ];
                }

                if (isset($request['sell_price_3']) && !empty($request['sell_price_3']) && isset($request['sell_amount_3']) && !empty($request['sell_amount_3'])) {
                    $feesZero3 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->sell_amount_3, 'sell', $request->sell_price_3);
                    if ($feesZero3) {
                        $msg3 = __("Sell Total (" . bcmulx($request->sell_price_3, $request->sell_amount_3) . ")  Should Not Less Than ") . $feesZero3;
                    }
                    $conditionSellOrders[] = [
                        'user_id' => $request['user_id'],
                        'condition_buy_id' => $buy->id,
                        'trade_coin_id' => $request['trade_coin_id'],
                        'base_coin_id' => $request['base_coin_id'],
                        'amount' => $request['sell_amount_3'],
                        'price' => $request['sell_price_3'],
                        'btc_rate' => $request['btcrate3'],
                        'maker_fees' => $request['maker_fees'],
                        'taker_fees' => $request['taker_fees'],
                        'created_at' => $dataNow,
                        'updated_at' => $dataNow
                    ];
                }

                if (isset($conditionSellOrders) && count($conditionSellOrders) > 0) {
                    if ($feesZero1 || $feesZero2 || $feesZero3) {
                        DBService::rollBack();
                        return [
                            'status' => false,
                            'message' => $msg1 . "</br>" . $msg2 . "</br>" . $msg3,
                        ];
                    }
                    $repo = new ConditionSellRepository(ConditionSell::class);
                    $repo->insert($conditionSellOrders);
                    storeBotException("ConditionSellOrderPlace", json_encode($conditionSellOrders));
                }
                $stopLimit = [];
                if (isset($request['stop_limit']) && !empty($request['stop_limit']) && isset($request['stop_price']) && !empty($request['stop_price'])) {
                    $stopLimit['user_id'] = Auth::id();
                    $stopLimit['condition_buy_id'] = $buy->id;
                    $stopLimit['stop_price'] = $request['stop_price'];
                    $stopLimit['stop_limit'] = $request['stop_limit'];
                    $stopLimit['amount'] = $buy->amount;
                    $stopLimit['order_type'] = 'sell';
                    $stopLimit['trade_coin_id'] = $request['trade_coin_id'];
                    $stopLimit['base_coin_id'] = $request['base_coin_id'];

                    $repo = new ConditionStopLimitRepository(ConditionStopLimit::class);
                    $repo->create($stopLimit);
                    storeBotException("ConditionStopLimitPlace", json_encode($stopLimit));
                }
                DBService::commit();
                broadcastWalletData($walletDetails->wallet_id);
                $repo = new CoinPairRepository(CoinPair::class);
                $coins = $repo->getDocs(['parent_coin_id' => $buy->base_coin_id, 'child_coin_id' => $buy->trade_coin_id])->first();
                dispatch(new ConditionBuyOrderProcessJob($coins))->onQueue('condition-buy-order');

                return response()->json([
                    'status' => true,
                    'message' => __("Conditional buy order has been placed successfully.")
                ]);
            } else {
                DBService::rollBack();
                return response()->json(['status' => false, 'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type]);
            }
        } catch (\Exception $e) {
            DBService::rollBack();

            return response()->json([
                'status' => false,
                'message' => __("Failed to place conditional buy order.")
            ]);
        }
    }

    /**
     * Get on order balance
     * @param $baseCoinId
     * @param $tradeCoinId
     * @param null $userId
     * @return mixed
     */
    public function getOnOrderBalance($baseCoinId, $tradeCoinId, $userId = null)
    {
        if ($userId == null) {
            $userId = Auth::id();
        }
        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId);
    }

    /**
     * Place Buy order of condition buy order
     * @param $coinPair
     * @return bool
     */
    public function conditionBuyProcess($coinPair)
    {
        storeBotException('ConditionBuyProcess', 'Coin Pair: ' . $coinPair->parent_coin_id . '_' . $coinPair->child_coin_id);
        $conditionBuys = $this->object->getDocs(['base_coin_id' => $coinPair->parent_coin_id, 'trade_coin_id' => $coinPair->child_coin_id, 'status' => 0]);
        try {
            foreach ($conditionBuys as $conditionBuy) {
                storeBotException('ConditionBuyProcess', 'Condition Buy Order Going to process id: ' . $conditionBuy->id);
                storeBotException('ConditionBuyProcess', 'Current Price: ' . $coinPair->price . ' Order Price: ' . $conditionBuy->price);
                if (bccompx($coinPair->price, $conditionBuy->price) <= 0) {
                    $input = [
                        'user_id' => $conditionBuy->user_id,
                        'condition_buy_id' => $conditionBuy->id,
                        'base_coin_id' => $conditionBuy->base_coin_id,
                        'trade_coin_id' => $conditionBuy->trade_coin_id,
                        'amount' => custom_number_format($conditionBuy->amount),
                        'virtual_amount' => bcmulx($conditionBuy->amount, bcdivx(random_int(20, 80), 100)),
                        'price' => custom_number_format($conditionBuy->price),
                        'btc_rate' => getBtcRate($conditionBuy->trade_coin_id),
                        'category' => $conditionBuy->category,
                        'maker_fees' => $conditionBuy->maker_fees,
                        'taker_fees' => $conditionBuy->taker_fees,
                        'is_conditioned' => 1,
                        'is_market' => 0,
                    ];
                    DBService::beginTransaction();
                    $buyRepo = new BuyOrderRepository(Buy::class);
                    $buy = $buyRepo->create($input);
                    if ($buy) {
                        broadcastOrderData($buy, 'buy', 'orderPlace', $buy->user_id);
                        storeBotException('ConditionBuyProcess', 'Buy Order Place ID:' . $buy->id);
                        $conditionBuy->update(['status' => 1]);
                        storeBotException('ConditionBuyProcess', 'Condition Buy Order is Closed');
                        DBService::commit();
                    } else {
                        DBService::rollBack();
                    }
                }
            }
            return true;
        } catch (\Exception $exception) {
            DBService::rollBack();
            storeException('ConditionBuyProcessError', 'Error: ' . $exception->getMessage() . ' ' . $exception->getLine());
            return false;
        }
    }

    /**
     * Place sell orders of a condition buy order
     * @param $buy
     * @return bool
     */
    public function conditionOrdersProcess($buy)
    {
        try {
            DBService::beginTransaction();
            storeBotException("ConditionOrdersProcess", "Conditioned Orders are going to process of condition buy id: " . $buy->condition_buy_id);
            $this->_getConditionedBuyOrderBasedSellOrders($buy->condition_buy_id);
            $this->_getConditionedBuyBasedStopLimit($buy->condition_buy_id);
            DBService::commit();

            return true;
        } catch (\Exception $e) {
            DBService::rollBack();
            storeException("ConditionOrdersProcessERROR", 'Caught an error' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            return false;
        }
    }

    private function _getConditionedBuyOrderBasedSellOrders($conditionedBuyOrderId)
    {
        $conditionSellRepo = new ConditionSellRepository(ConditionSell::class);
        $conditionedSellOrders = $conditionSellRepo->getDocs(['condition_buy_id' => $conditionedBuyOrderId, 'status' => 0]);
        if (!$conditionedSellOrders->isEmpty()) {
            storeBotException("info", "Conditional sell orders are found!");
            $sellOrderRepo = new SellOrderRepository(Sell::class);
            foreach ($conditionedSellOrders as $conditionedSellOrder) {
                $input = [
                    'user_id' => $conditionedSellOrder->user_id,
                    'condition_buy_id' => $conditionedSellOrder->condition_buy_id,
                    'trade_coin_id' => $conditionedSellOrder->trade_coin_id,
                    'base_coin_id' => $conditionedSellOrder->base_coin_id,
                    'amount' => custom_number_format($conditionedSellOrder->amount),
                    'virtual_amount' => bcmulx($conditionedSellOrder->amount, bcdivx(random_int(20, 80), 100)),
                    'price' => custom_number_format($conditionedSellOrder->price),
                    'btc_rate' => $conditionedSellOrder->btc_rate,
                    'is_market' => 0,
                    'category' => $conditionedSellOrder->category,
                    'is_conditioned' => 1,
                    'maker_fees' => $conditionedSellOrder->maker_fees,
                    'taker_fees' => $conditionedSellOrder->taker_fees,
                ];

                if ($sell = $sellOrderRepo->create($input)) {
                    broadcastOrderData($sell, 'sell', 'orderPlace', $sell->user_id);
                    $conditionedSellOrder->update(['status' => 1]);
                }
            }
        }
    }

    private function _getConditionedBuyBasedStopLimit($conditionedBuyOrderID)
    {
        $conditionedStopLimitRepo = new ConditionStopLimitRepository(ConditionStopLimit::class);
        $conditionedStopLimit = $conditionedStopLimitRepo->getDocs(['condition_buy_id' => $conditionedBuyOrderID, 'status' => 0])->first();
        if (!empty($conditionedStopLimit)) {
            storeBotException("Info", "Found conditioned stop limit.");
            $conditionedStopLimitData = [
                'user_id' => $conditionedStopLimit->user_id,
                'condition_buy_id' => $conditionedStopLimit->condition_buy_id,
                'amount' => custom_number_format($conditionedStopLimit->amount),
                'stop' => custom_number_format($conditionedStopLimit->stop_price),
                'limit_price' => custom_number_format($conditionedStopLimit->stop_limit),
                'order' => 'sell',
                'is_conditioned' => 1,
                'category' => $conditionedStopLimit->category,
                'trade_coin_id' => $conditionedStopLimit->trade_coin_id,
                'base_coin_id' => $conditionedStopLimit->base_coin_id
            ];
            $stopLimitRepo = new StopLimitRepository(StopLimit::class);
            $createStopLimit = $stopLimitRepo->create($conditionedStopLimitData);
            if ($createStopLimit) {
                storeBotException("Info", "Inserted conditioned stop limit in stop limits table.");
                $conditionedStopLimit->update(['status' => 1]);
            } else {
                storeBotException("Data Error", "Failed to insert conditioned stop limit in stop limits table");
            }
        } else {
            storeBotException("Data Error", "No conditioned stop limit found!");
        }
    }
}
