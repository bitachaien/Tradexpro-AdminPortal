<?php

namespace App\Http\Services;

use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\FutureTradeLongShort;
use App\Model\FutureTradeTransactionHistory;
use App\Model\FutureWallet;
use App\Model\Sell;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Repositories\DashboardRepository;
use App\Model\SelectedCoinPair;
use App\Http\Repositories\CoinPairRepository;

class FutureTradeBuySellOrderService
{

    private $dashBoardRepository;

    public function __construct()
    {
        $this->dashBoardRepository = new DashboardRepository;
    }

    public function checkIsolatedCrossPositionOpenOrder($user, $coinPairDetails, $marginMode)
    {
        $orderList = FutureTradeLongShort::where('user_id', $user->id)
            ->whereNull('parent_id')
            ->where('base_coin_id', $coinPairDetails->parent_coin_id)
            ->where('trade_coin_id', $coinPairDetails->child_coin_id)
            ->when(isset($marginMode), function ($query) use ($marginMode) {
                if ($marginMode == MARGIN_MODE_CROSS) {
                    $query->where('margin_mode', MARGIN_MODE_ISOLATED);
                } else {
                    $query->where('margin_mode', MARGIN_MODE_CROSS);
                }
            })
            ->whereIn('is_position', [FUTURE_TRADE_IS_POSITION, FUTURE_TRADE_HOLD_POSITION])
            ->where('status', STATUS_INACTIVE)
            ->get();

        if ($orderList->count() > 0) {
            if ($marginMode == MARGIN_MODE_ISOLATED) {
                return responseData(true, __('Margin Mode Cross order are present in open order or position order!'));
            } else {
                return responseData(true, __('Margin Mode Isolated order are present in open order or position order!'));
            }
        } else {
            return responseData(false, __('No order is found in Isolated/Cross Margin Mode!'));
        }
    }
    public function placedBuyOrder($request)
    {
        $user = auth()->user();

        $coinPairDetails = CoinPair::with(['parent_coin', 'child_coin'])
            ->where('id', $request->coin_pair_id)
            ->where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->first();


        if (isset($coinPairDetails)) {
            $response_checkOrder = $this->checkIsolatedCrossPositionOpenOrder($user, $coinPairDetails, $request->margin_mode);

            if ($response_checkOrder['success']) {
                return $response_checkOrder;
            }

            if ($request->leverage_amount < 0 || $request->leverage_amount > $coinPairDetails->max_leverage) {
                $response = responseData(false, __('Invalid Leverage amount!'));
                return $response;
            }

            $userWalletDetails = FutureWallet::where('user_id', $user->id)
                ->where('coin_id', $coinPairDetails->parent_coin_id)->first();
            if (isset($userWalletDetails)) {

                $order_type = $request->order_type;
                $amount = $request->amount;
                $marketPrice = $coinPairDetails->price;
                $orderPrice = $marketPrice;
                $amount_type = $request->amount_type;
                $leverage_amount = $request->leverage_amount;

                if ($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) {
                    $orderPrice = $request->price;
                }

                if (($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) && $request->price < $marketPrice) {
                    return responseData(false, __('Price must be greater than market price!'));
                }

                if ($amount_type == AMOUNT_TYPE_BASE) {
                    $baseCoinAmount = $amount;
                    $tradeCoinAmount = bcdivx($amount, $orderPrice, 8);
                } else {
                    $baseCoinAmount = bcmulx($amount, $orderPrice, 8);
                    $tradeCoinAmount = $amount;
                }

                $walletBalance = $userWalletDetails->balance;
                $totalAmount = $walletBalance * $request->leverage_amount;

                if (isset($request->stop_loss) && ($request->stop_loss > $marketPrice || $request->stop_loss > $orderPrice)) {
                    return responseData(false, __('Stop loss price must be less than entry price and market price!'));
                }

                if (isset($request->take_profit) && ($request->take_profit < $marketPrice)) {
                    return responseData(false, __('Take profit price must be greater than market price!'));
                }

                $calculateCost = calculateCostForFutureTrade($coinPairDetails, $order_type, $amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);

                if ($calculateCost['totalCostLong'] > $walletBalance) {
                    $response = responseData(false, __('Insufficient margin amount'));
                    return $response;
                }

                $isPosition = FUTURE_TRADE_IS_POSITION;
                $avg_close_price = 0;

                if ($request->margin_mode == MARGIN_MODE_CROSS) {
                    $avg_close_price = ($coinPairDetails->price + $orderPrice) / 2;

                    if ($avg_close_price < $coinPairDetails->price) {
                        $isPosition = FUTURE_TRADE_HOLD_POSITION;
                    }
                }

                if (($order_type == STOP_LIMIT_ORDER || $order_type == STOP_MARKET_ORDER) && $request->stop_price > $marketPrice) {
                    $isPosition = FUTURE_TRADE_HOLD_POSITION;
                }

                $newOrder = new FutureTradeLongShort;
                $newOrder->side = TRADE_TYPE_BUY;
                $newOrder->amount_in_base_coin = $baseCoinAmount;
                $newOrder->amount_in_trade_coin = $tradeCoinAmount;
                $newOrder->margin = $calculateCost['longCost'];
                $newOrder->uid = generateUID();
                $newOrder->user_id = $user->id;
                $newOrder->base_coin_id = $coinPairDetails->parent_coin_id;
                $newOrder->trade_coin_id = $coinPairDetails->child_coin_id;
                $newOrder->entry_price = $coinPairDetails->price;
                $newOrder->price = $orderPrice;
                $newOrder->liquidation_price = calculateLiquidationPrice($request, $coinPairDetails);
                $newOrder->fees = $calculateCost['fundingFeesLong'];
                $newOrder->comission = $calculateCost['commissionFeesLong'];
                $newOrder->leverage = $leverage_amount;
                $newOrder->margin_mode = $request->margin_mode;
                $newOrder->avg_close_price = $avg_close_price;
                $newOrder->trade_type = FUTURE_TRADE_TYPE_OPEN;
                $newOrder->is_position = $isPosition;
                $newOrder->order_type = $order_type;
                $newOrder->is_market = ($order_type == MARKET_ORDER) ? 1 : 0;
                $newOrder->stop_price = ($order_type == STOP_LIMIT_ORDER || $order_type == STOP_MARKET_ORDER) ? $request->stop_price : 0;
                $newOrder->save();

                if (isset($request->take_profit)) {
                    storeCloseOrderStopLossTakeProfit($newOrder, FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE, $request->take_profit);
                }

                if (isset($request->stop_loss)) {
                    storeCloseOrderStopLossTakeProfit($newOrder, FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE, $request->stop_loss);
                }

                $userWalletDetails->decrement('balance', $calculateCost['totalCostLong']);

                createFutureTradeTransaction(
                    $newOrder->user_id,
                    $userWalletDetails->id,
                    FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES,
                    $calculateCost['fundingFeesLong'],
                    $userWalletDetails->coin_type,
                    coinPairSymbol($newOrder->base_coin_id, $newOrder->trade_coin_id),
                    $coinPairDetails->id,
                    $newOrder->id
                );

                createFutureTradeTransaction(
                    $newOrder->user_id,
                    $userWalletDetails->id,
                    FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION,
                    $calculateCost['commissionFeesLong'],
                    $userWalletDetails->coin_type,
                    coinPairSymbol($newOrder->base_coin_id, $newOrder->trade_coin_id),
                    $coinPairDetails->id,
                    $newOrder->id
                );

                $response = responseData(true, __('Long Order is placed successfully!'), $newOrder);
            } else {
                $response = responseData(false, 'Wallet is not found!');
            }
        } else {
            $response = responseData(false, 'Coin does not exist!');
        }

        return $response;
    }

    public function updateProfitLossLongShortOrder($request)
    {
        $buyOrderDetails = FutureTradeLongShort::where('uid', $request->order_uid)
            ->first();

        if (isset($buyOrderDetails)) {
            $coinPairDetails = CoinPair::where('parent_coin_id', $buyOrderDetails->base_coin_id)
                ->where('child_coin_id', $buyOrderDetails->trade_coin_id)
                ->first();

            if (isset($coinPairDetails)) {
                $marketPrice = $coinPairDetails->price;
                $entryPrice = $buyOrderDetails->entry_price;

                if ($buyOrderDetails->side == TRADE_TYPE_BUY) {
                    if (isset($request->stop_loss) && ($request->stop_loss > $marketPrice || $request->stop_loss > $entryPrice)) {
                        return responseData(false, __('Stop loss price must be less than entry price and market price!'));
                    }

                    if (isset($request->take_profit) && ($request->take_profit < $marketPrice)) {
                        return responseData(false, __('Take profit price must be greater than market price!'));
                    }
                } else {
                    if (isset($request->stop_loss) && ($request->stop_loss < $entryPrice || $request->stop_loss < $marketPrice)) {
                        return responseData(false, __('Stop loss price must be greater than entry price and market price!'));
                    }

                    if (isset($request->take_profit) && ($request->take_profit > $entryPrice || $request->take_profit > $marketPrice)) {
                        return responseData(false, __('Take profit price must be less than entry price and market price!'));
                    }
                }

                if (isset($request->take_profit)) {
                    $takeProfitOrderDetails = FutureTradeLongShort::where('parent_id', $buyOrderDetails->id)
                        ->where('take_profit_price', '<>', 0)
                        ->first();

                    if (isset($takeProfitOrderDetails)) {
                        $takeProfitOrderDetails->take_profit_price = $request->take_profit;
                        $takeProfitOrderDetails->status = 0;
                        $takeProfitOrderDetails->save();
                    } else {
                        storeCloseOrderStopLossTakeProfit($buyOrderDetails, FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE, $request->take_profit);
                    }
                }

                if (isset($request->stop_loss)) {
                    $stopLossOrderDetails = FutureTradeLongShort::where('parent_id', $buyOrderDetails->id)
                        ->where('stop_loss_price', '<>', 0)
                        ->first();

                    if (isset($stopLossOrderDetails)) {
                        $stopLossOrderDetails->stop_loss_price = $request->stop_loss;
                        $takeProfitOrderDetails->status = 0;
                        $stopLossOrderDetails->save();
                    } else {
                        storeCloseOrderStopLossTakeProfit($buyOrderDetails, FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE, $request->stop_loss);
                    }
                }

                return responseData(true, __('Profit and loss is updated!'));
            } else {
                return responseData(false, __('Coin pair does not exist!'));
            }
        } else {
            return responseData(false, __('Invalid Request!'));
        }
    }

    public function placedSellOrder($request)
    {
        $user = auth()->user();

        $coinPairDetails = CoinPair::with(['parent_coin', 'child_coin'])
            ->where('id', $request->coin_pair_id)
            ->where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->first();


        if (isset($coinPairDetails)) {
            $response_checkOrder = $this->checkIsolatedCrossPositionOpenOrder($user, $coinPairDetails, $request->margin_mode);

            if ($response_checkOrder['success']) {
                return $response_checkOrder;
            }

            if ($request->leverage_amount < 0 || $request->leverage_amount > $coinPairDetails->max_leverage) {
                $response = responseData(false, __('Invalid Leverage amount!'));
                return $response;
            }

            $userWalletDetails = FutureWallet::where('user_id', $user->id)
                ->where('coin_id', $coinPairDetails->parent_coin_id)->first();
            if (isset($userWalletDetails)) {

                $order_type = $request->order_type;
                $amount = $request->amount;
                $marketPrice = $coinPairDetails->price;
                $orderPrice = $coinPairDetails->price;
                $amount_type = $request->amount_type;
                $leverage_amount = $request->leverage_amount;

                $walletBalance = $userWalletDetails->balance;

                if ($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) {
                    $orderPrice = $request->price;
                }

                if (($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) && $request->price > $marketPrice) {
                    return responseData(false, __('Price must be less than market price!'));
                }

                if ($amount_type == AMOUNT_TYPE_BASE) {
                    $baseCoinAmount = $amount;
                    $tradeCoinAmount = bcdivx($amount, $orderPrice, 8);
                } else {
                    $baseCoinAmount = bcmulx($amount, $orderPrice, 8);
                    $tradeCoinAmount = $amount;
                }

                if ($orderPrice > $marketPrice) {
                    return responseData(false, __('Price should be less than market price!'));
                }

                if (isset($request->stop_loss) && ($request->stop_loss < $orderPrice || $request->stop_loss < $marketPrice)) {
                    return responseData(false, __('Stop loss price must be greater than entry price and market price!'));
                }

                if (isset($request->take_profit) && ($request->take_profit > $orderPrice || $request->take_profit > $marketPrice)) {
                    return responseData(false, __('Take profit price must be less than entry price and market price!'));
                }

                $calculateCost = calculateCostForFutureTrade($coinPairDetails, $order_type, $amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);

                if ($calculateCost['totalCostShort'] > $walletBalance) {
                    $response = responseData(false, __('Insufficient margin amount'));
                    return $response;
                }

                $isPosition = FUTURE_TRADE_IS_POSITION;
                $avg_close_price = 0;

                if ($request->margin_mode == MARGIN_MODE_CROSS) {
                    $avg_close_price = ($coinPairDetails->price + $orderPrice) / 2;

                    if ($avg_close_price > $coinPairDetails->price) {
                        $isPosition = FUTURE_TRADE_HOLD_POSITION;
                    }
                }

                if (($order_type == STOP_LIMIT_ORDER || $order_type == STOP_MARKET_ORDER) && $request->stop_price < $marketPrice) {
                    $isPosition = FUTURE_TRADE_HOLD_POSITION;
                }

                $newOrder = new FutureTradeLongShort;
                $newOrder->side = TRADE_TYPE_SELL;
                $newOrder->amount_in_base_coin = $baseCoinAmount;
                $newOrder->amount_in_trade_coin = $tradeCoinAmount;
                $newOrder->margin = $calculateCost['shortCost'];
                $newOrder->uid = generateUID();
                $newOrder->user_id = $user->id;
                $newOrder->base_coin_id = $coinPairDetails->parent_coin_id;
                $newOrder->trade_coin_id = $coinPairDetails->child_coin_id;
                $newOrder->entry_price = $coinPairDetails->price;
                $newOrder->price = $orderPrice;
                $newOrder->liquidation_price = calculateLiquidationPrice($request, $coinPairDetails);
                $newOrder->fees = $calculateCost['fundingFeesShort'];
                $newOrder->comission = $calculateCost['commissionFeesShort'];
                $newOrder->leverage = $leverage_amount;
                $newOrder->margin_mode = $request->margin_mode;
                $newOrder->avg_close_price = $avg_close_price;
                $newOrder->trade_type = FUTURE_TRADE_TYPE_OPEN;
                $newOrder->is_position = $isPosition;
                $newOrder->order_type = $order_type;
                $newOrder->is_market = ($request->order_type == MARKET_ORDER) ? 1 : 0;
                $newOrder->stop_price = ($order_type == STOP_LIMIT_ORDER || $order_type == STOP_MARKET_ORDER) ? $request->stop_price : 0;
                $newOrder->save();

                if (isset($request->take_profit)) {
                    storeCloseOrderStopLossTakeProfit($newOrder, FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE, $request->take_profit);
                }

                if (isset($request->stop_loss)) {
                    storeCloseOrderStopLossTakeProfit($newOrder, FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE, $request->stop_loss);
                }

                $userWalletDetails->decrement('balance', $calculateCost['totalCostShort']);

                createFutureTradeTransaction(
                    $newOrder->user_id,
                    $userWalletDetails->id,
                    FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES,
                    $calculateCost['fundingFeesShort'],
                    $userWalletDetails->coin_type,
                    coinPairSymbol($newOrder->base_coin_id, $newOrder->trade_coin_id),
                    $coinPairDetails->id,
                    $newOrder->id
                );

                createFutureTradeTransaction(
                    $newOrder->user_id,
                    $userWalletDetails->id,
                    FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION,
                    $calculateCost['commissionFeesShort'],
                    $userWalletDetails->coin_type,
                    coinPairSymbol($newOrder->base_coin_id, $newOrder->trade_coin_id),
                    $coinPairDetails->id,
                    $newOrder->id
                );

                $response = responseData(true, __('Short Order is placed successfully!'), $newOrder);
            } else {
                $response = responseData(false, 'Wallet is not found!');
            }
        } else {
            $response = responseData(false, 'Coin does not exist!');
        }

        return $response;
    }

    public function canceledLongShortOrder($request, $user)
    {
        $orderDetails = FutureTradeLongShort::where('uid', $request->uid)
            ->where('user_id', $user->id)
            ->first();

        if (isset($orderDetails)) {
            if ($orderDetails->status == 5) {
                return responseData(false, __('Your order is already canceled!'));
            }

            if ($orderDetails->take_profit_price > 0 || $orderDetails->stop_loss_price > 0) {
                $orderDetails->status = STATUS_DELETED;
                $orderDetails->save();

                return responseData(true, __('Your order is canceled successfully!'));
            } elseif ($orderDetails->is_position != STATUS_ACTIVE) {
                $orderDetails->status = STATUS_DELETED;
                $orderDetails->save();

                return responseData(true, __('Your order is canceled successfully!'));
            } else {
                return responseData(false, __('You can not cancel this order!'));
            }
        } else {
            return responseData(false, __('Invalid Request!'));
        }
    }

    public function getLongShortPositionOrderList($request)
    {
        if (!isset($request->base_coin_id) || !isset($request->trade_coin_id)) {
            return responseData(false, __('Base Coin Id and Trade Coin Id is required!'));
        }

        $user = auth()->user();

        $orderList = FutureTradeLongShort::when(isset($request->side), function ($query) use ($request) {
            $query->where('side', $request->side);
        })
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->where('base_coin_id', $request->base_coin_id)
            ->where('trade_coin_id', $request->trade_coin_id)
            ->where('is_position', STATUS_ACTIVE)
            ->where('status', STATUS_INACTIVE)
            ->orderBy('id', 'desc')
            ->get();

        $orderList->map(function ($query) {
            $query['profit_loss_calculation'] = calculatePositionData($query->id);
        });

        $response = responseData(true, __('Long Short Position order list'), $orderList);
        return $response;
    }

    public function getLongShortOpenOrderList($request)
    {
        if (!isset($request->base_coin_id) || !isset($request->trade_coin_id)) {
            return responseData(false, __('Base Coin Id and Trade Coin Id is required!'));
        }

        $user = auth()->user();

        $orderList = FutureTradeLongShort::when(isset($request->side), function ($query) use ($request) {
            $query->where('side', $request->side);
        })
            ->where('user_id', $user->id)
            ->where('base_coin_id', $request->base_coin_id)
            ->where('trade_coin_id', $request->trade_coin_id)
            ->where('is_position', '<>', FUTURE_TRADE_IS_POSITION)
            ->where('status', STATUS_INACTIVE)
            ->orderBy('id', 'desc')
            ->get();

        $orderList->map(function ($query) {
            $query['profit_loss_calculation'] = calculatePositionData($query->id);
        });

        $response = responseData(true, __('Long Short order list'), $orderList);

        return $response;
    }

    public function orderDetails($request)
    {
        $user = auth()->user();

        if ($request->type == TRADE_TYPE_BUY) {
            $orderDetails = Buy::where('id', $request->id)
                ->where('user_id', $user->id)
                ->where('is_future', 1)
                ->where('is_position', RUNNING_ORDER)
                ->first();
        } else {
            $orderDetails = Sell::where('id', $request->id)
                ->where('user_id', $user->id)
                ->where('is_future', 1)
                ->where('is_position', RUNNING_ORDER)
                ->first();
        }

        if (isset($orderDetails)) {
            $entryPrice = $orderDetails->price;
            $exitPrice = $orderDetails->price + 500;
            $quantity = $orderDetails->amount / $orderDetails->price;
            $leverage = $orderDetails->leverage;

            $pnl = ($exitPrice - $entryPrice) * $quantity;
            $roe = ($exitPrice - $entryPrice) * $quantity * $leverage;
            $size = $orderDetails->amount * $leverage;
            $margin = $orderDetails->amount / $orderDetails->leverage;
            $risk = 0;
            $marketPrice = 0;
            $liquidationPrice = $entryPrice - ($entryPrice / $leverage);
            $takeProfit = $orderDetails->take_profit;
            $stopLost = $orderDetails->stop_loss;
            $status = $orderDetails->status;

            $data['entry_price'] = $entryPrice;
            $data['exit_price'] = $exitPrice;
            $data['quantity'] = $quantity;
            $data['leverage'] = $leverage;
            $data['profit_loss'] = $pnl;
            $data['roe'] = $roe;
            $data['size'] = $size;
            $data['margin'] = $margin;
            $data['risk'] = $risk;
            $data['marketPrice'] = $marketPrice;
            $data['liquidationPrice'] = $liquidationPrice;
            $data['takeProfit'] = $takeProfit;
            $data['stopLost'] = $stopLost;
            $data['status'] = $status;


            $response = responseData(true, __('Order Details'), $data);
        } else {
            $response = responseData(false, __('Invalid Request!'));
        }

        return $response;
    }

    public function getOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 50;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $volume = $this->getTotalVolume($request->base_coin_id, $request->trade_coin_id);
            if ($request->order_type == 'sell') {
                $sellOrderService = new SellOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id);
                }
                $data['order_type'] = 'sell';
                $data['total_volume'] = $volume['total_sell_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                $buyOrderService = new BuyOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id);
                }
                $data['order_type'] = 'buy';
                $data['total_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                $sellOrderService = new SellOrderService();
                $buyOrderService = new BuyOrderService();

                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['buy_orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                    $data['sell_orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
                } else {
                    $data['buy_orders'] = $buyOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
                    $data['sell_orders'] = $sellOrderService->getAllOrders($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
                }
                $data['order_type'] = 'buy_sell';
                $data['total_sell_volume'] = $volume['total_sell_amount'];
                $data['total_buy_volume'] = $volume['total_buy_amount'];
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            storeException('get all order exception -> ', $e->getMessage());
        }

        return $response;
    }

    public function getTotalVolume($baseCoinId, $tradeCoinId)
    {
        $buyOrderService = new BuyOrderService();
        $data['total_buy_amount'] = truncate_num($buyOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
        $data['buy_price'] = truncate_num($buyOrderService->getPrice($baseCoinId, $tradeCoinId));
        $sellOrderService = new SellOrderService();
        $data['total_sell_amount'] = truncate_num($sellOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
        $data['sell_price'] = truncate_num($sellOrderService->getPrice($baseCoinId, $tradeCoinId));

        return $data;
    }

    public function getOrderData($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        $baseCoinId = $request->base_coin_id;
        $tradeCoinId = $request->trade_coin_id;
        try {
            if (Auth::guard('api')->check()) {
                if (empty($baseCoinId) || empty($tradeCoinId)) {

                    $tradeCoinId = $this->_getTradeCoin();
                    $baseCoinId = $this->_getBaseCoin();

                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                } else {
                    $data['base_coin_id'] = $baseCoinId;
                    $data['trade_coin_id'] = $tradeCoinId;
                }
                $baseCoinData = $this->getCoinPair($baseCoinId, $tradeCoinId);

                $data['maintenance_margin_rate'] = $baseCoinData->maintenance_margin_rate;
                $data['minimum_amount_future'] = $baseCoinData->minimum_amount_future;
                $data['leverage_fee'] = $baseCoinData->leverage_fee;
                $data['max_leverage'] = $baseCoinData->max_leverage;
                $data['coin_pair_id'] = $baseCoinData->id;
                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $baseCoinData->high;
                $data['total']['trade_wallet']['low'] = $baseCoinData->low;
                $data['total']['trade_wallet']['volume'] = $baseCoinData->volume;
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $baseCoinData->price_change;
                $data['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal ?? 8;


                $wallet = $this->getFutureTradeUserWalletDetails(getUserId(), $baseCoinData->parent_coin_id)['data'];

                $data['total']['base_wallet']['balance'] = $wallet->balance;
                $data['total']['base_wallgetOrderDataet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;

                $data['fees'] = calculated_fee_limit(getUserId());
                $onOrder = $this->getOnOrderBalance($baseCoinId, $tradeCoinId);
                $data['on_order']['trade_wallet'] = $onOrder['total_sell'];
                $data['on_order']['base_wallet'] = $onOrder['total_buy'];

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;
            } else {
                if (empty($tradeCoinId) || empty($baseCoinId)) {
                    $tradeCoinId = 1;
                    $baseCoinId = 2;
                }
                $repo = new CoinPairRepository(CoinPair::class);
                $baseCoinData = $repo->getFutureTradeCoinPairsData($baseCoinId, $tradeCoinId);

                $data['base_coin_id'] = $baseCoinData->parent_coin_id;
                $data['trade_coin_id'] = $baseCoinData->child_coin_id;
                $data['total']['trade_wallet']['balance'] = $baseCoinData->balance;
                $data['total']['trade_wallet']['coin_type'] = $baseCoinData->child_coin_name;
                $data['total']['trade_wallet']['full_name'] = $baseCoinData->child_full_name;
                $data['total']['trade_wallet']['high'] = $baseCoinData->high;
                $data['total']['trade_wallet']['low'] = $baseCoinData->low;
                $data['total']['trade_wallet']['volume'] = $baseCoinData->volume;
                $data['total']['trade_wallet']['last_price'] = $baseCoinData->last_price;
                $data['total']['trade_wallet']['price_change'] = $baseCoinData->price_change;
                $data['total']['trade_wallet']['pair_decimal'] = $baseCoinData->pair_decimal ?? 8;

                $data['total']['base_wallet']['balance'] = 0;
                $data['total']['base_wallet']['coin_type'] = $baseCoinData->parent_coin_name;
                $data['total']['base_wallet']['full_name'] = $baseCoinData->parent_full_name;

                $data['fees'] = 0;
                $data['on_order']['trade_wallet'] = 0;
                $data['on_order']['base_wallet'] = 0;

                $price = $this->getTotalVolume($baseCoinId, $tradeCoinId);
                $data['sell_price'] = $price['sell_price'] > 0 ? $price['sell_price'] : $baseCoinData->last_price;
                $data['buy_price'] = $price['buy_price'] > 0 ? $price['buy_price'] : $baseCoinData->last_price;
            }
            $data['base_coin'] = get_coin_type($data['base_coin_id']);
            $data['trade_coin'] = get_coin_type($data['trade_coin_id']);
            $data['exchange_pair'] = $data['trade_coin'] . '_' . $data['base_coin'];
            $data['exchange_coin_pair'] = $data['trade_coin'] . '/' . $data['base_coin'];

            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];

            return $response;
        } catch (\Exception $exception) {
            storeException('get future order data exception--> ', $exception->getMessage());
            return [
                'status' => false,
                'message' => __('Something went wrong. Please try again!' . getError($exception)),
                'data' => []
            ];
        }
    }

    public function _getTradeCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();

        if (!empty($selectedCoinPair)) {
            return $selectedCoinPair->trade_coin_id;
        } else {
            return 1;
        }
    }

    public function _getBaseCoin()
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $selectedCoinPair->base_coin_id;
        } else {
            return 2;
        }
    }

    public function getCoinPair($baseCoinId, $tradeCoinId)
    {
        if (empty($tradeCoinId) || empty($baseCoinId)) {
            $tradeCoinId = $this->_getTradeCoin();
            $baseCoinId = $this->_getBaseCoin();
        } else {
            $this->_setTradeCoin($tradeCoinId);
            $this->_setBaseCoin($baseCoinId);
        }

        $repo = new CoinPairRepository(CoinPair::class);

        return $repo->getFutureTradeCoinPairsData($baseCoinId, $tradeCoinId);
    }

    public function _setTradeCoin($tradeCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $repo->updateWhere(['user_id' => getUserId()], ['trade_coin_id' => $tradeCoinId]);
        } else {
            return SelectedCoinPair::create(['user_id' => getUserId(), 'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function _setBaseCoin($baseCoinId)
    {
        $repo = new DashboardRepository();
        $selectedCoinPair = $repo->getDocs(['user_id' => getUserId()])->first();
        if (!empty($selectedCoinPair)) {
            return $repo->updateWhere(['user_id' => getUserId()], ['base_coin_id' => $baseCoinId]);
        } else {
            return SelectedCoinPair::create(['user_id' => getUserId(), 'trade_coin_id' => 1, 'base_coin_id' => 2]);
        }
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId)
    {
        $data['total_buy'] = $this->dashBoardRepository->getOnOrderBalance($baseCoinId);
        $data['total_sell'] = $this->dashBoardRepository->getOnOrderBalance($tradeCoinId);

        return $data;
    }

    public function getDashboardMarketTradeDataTwo($base_coin_id, $trade_coin_id, $limit)
    {
        $transactionService = new TransactionService();
        return $transactionService->getAllTradeHistory($base_coin_id, $trade_coin_id)->limit($limit)->orderBy('id', 'desc')->get();
    }

    public function getMarketTransactions($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 50;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $transactionService = new TransactionService();
            if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                $data['transactions'] = $transactionService->getAllTradeHistory($request->base_coin_id, $request->trade_coin_id)->limit($perPage)->get();
            } else {
                $data['transactions'] = $transactionService->getAllTradeHistory($request->base_coin_id, $request->trade_coin_id)->paginate($perPage)->appends($request->all());
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            storeException('get market trade history exception -> ', $e->getMessage());
        }

        return $response;
    }

    public function getMyOrders($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $userId = $request->userId ?? getUserId();
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            if ($request->order_type == 'sell') {
                $sellOrderService = new SellOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->limit(20)->get();
                } else {
                    $data['orders'] = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else if ($request->order_type == 'buy') {
                $buyOrderService = new BuyOrderService();
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->limit(20)->get();
                } else {
                    $data['orders'] = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->paginate($perPage)->appends($request->all());
                }
                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            } else {
                $sellOrderService = new SellOrderService();
                $sellOrders = $sellOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->get()->toArray();
                $buyOrderService = new BuyOrderService();
                $buyOrders = $buyOrderService->getMyOrders($request->base_coin_id, $request->trade_coin_id, $userId)->get()->toArray();
                $data['orders'] = array_merge($buyOrders, $sellOrders);
                $data['buy_orders'] = $buyOrders;
                $data['sell_orders'] = $sellOrders;
                usort($data['orders'], function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });

                $response = [
                    'status' => true,
                    'message' => '',
                    'data' => $data
                ];
            }
        } catch (\Exception $e) {
            storeException('get my order exception -> ', $e->getMessage());
        }

        return $response;
    }

    public function getMyTradeHistory($request)
    {
        $response = [
            'status' => false,
            'message' => __('Something went wrong'),
            'data' => []
        ];
        try {
            $userId = isset($request->user_id) ? $request->user_id : getUserId();
            $setting_per_page = isset(allsetting()['user_pagination_limit']) ? allsetting()['user_pagination_limit'] : 10;
            $perPage = empty($request->per_page) ? $setting_per_page : $request->per_page;

            $transactionService = new TransactionService();
            if ($request->per_page == 'all') {
                $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, 0)->get();
            } else {
                if (isset($request->dashboard_type) && $request->dashboard_type == 'dashboard') {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, $request->duration ?? null)->limit(20)->get();
                } else {
                    $data['transactions'] = $transactionService->getMyTradeHistory($request->base_coin_id, $request->trade_coin_id, $userId, $request->order_type ?? null, $request->duration ?? null)->paginate($perPage)->appends($request->all());
                }
            }
            $response = [
                'status' => true,
                'message' => __('Data get successfully'),
                'data' => $data
            ];
        } catch (\Exception $e) {
            storeException('get my trade history exception -> ', $e->getMessage());
        }

        return $response;
    }

    public function getFutureTradeMarketPairData($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;

        $coinPairDetails = CoinPair::where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->paginate($limit, ['*'], 'page', $offset);

        $response = responseData(true, __('Future Trade Market Pair Data'), $coinPairDetails);

        return $response;
    }

    public function getFutureTradeOrderCalculation($request)
    {
        if (!isset($request->future_trade_order_id)) {
            return responseData(false, __('Future Trade order UID is required!'));
        }

        $responseData = calculatePositionData($request->future_trade_order_id, $request->exit_price);

        return $responseData;
    }

    public function closeLongShortOrder($request, $user)
    {
        $coinPairDetails = CoinPair::where('id', $request->coin_pair_id)
            ->where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->first();

        if (!isset($coinPairDetails)) {
            return responseData(false, __('Coin Pair details not found!'));
        }

        if ($request->order_type == LIMIT_ORDER || $request->order_type == MARKET_ORDER) {
            return $this->closeMarketAndLimitLongShortOrder($user, $coinPairDetails, $request);
        } else {
            return $this->closeStopMarketAndStopLimitLongShortOrder($user, $coinPairDetails, $request);
        }
    }

    public function closeStopMarketAndStopLimitLongShortOrder($user, $coinPairDetails, $request)
    {
        try {
            $side = $request->side;
            $order_type = $request->order_type;
            $amount = $request->amount;
            $marketPrice = $coinPairDetails->price;
            $orderPrice = $marketPrice;
            $amount_type = $request->amount_type;
            $leverage_amount = $request->leverage_amount;
            $margin_mode = $request->margin_mode;

            $checkCloseOrder = FutureTradeLongShort::where('user_id', $user->id)
                ->whereNull('parent_id')
                ->where('order_type', $order_type)
                ->where('side', $side)
                ->where('base_coin_id', $coinPairDetails->parent_coin_id)
                ->where('trade_coin_id', $coinPairDetails->child_coin_id)
                ->where('trade_type', FUTURE_TRADE_TYPE_STOP_MARKET_LIMIT_CLOSE)
                ->where('is_position', FUTURE_TRADE_STOP_MARKET_LIMIT_POSITION)
                ->where('status', STATUS_INACTIVE)
                ->first();

            if (isset($checkCloseOrder)) {
                return responseData(false, __('You have already a close order!'));
            }

            $orderList = FutureTradeLongShort::where('user_id', $user->id)
                ->whereNull('parent_id')
                ->where('order_type', $order_type)
                ->where('side', $side)
                ->where('base_coin_id', $coinPairDetails->parent_coin_id)
                ->where('trade_coin_id', $coinPairDetails->child_coin_id)
                ->where('is_position', FUTURE_TRADE_IS_POSITION)
                ->where('status', STATUS_INACTIVE)
                ->get();


            if ($orderList->count() > 0) {
                if ($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) {
                    $orderPrice = $request->price;
                }

                if ($request->stop_price > $marketPrice) {
                    $tradeCoinAmount = ($amount_type == AMOUNT_TYPE_TRADE) ? $amount : bcdivx($amount, $orderPrice, 8);
                    $baseCoinAmount = bcmulx($tradeCoinAmount, $orderPrice, 8);

                    $closeOrder = new FutureTradeLongShort;
                    $closeOrder->uid = generateUID();
                    $closeOrder->side = $side;
                    $closeOrder->user_id = $user->id;
                    $closeOrder->amount_in_base_coin = $baseCoinAmount;
                    $closeOrder->amount_in_trade_coin = $tradeCoinAmount;
                    $closeOrder->base_coin_id = $coinPairDetails->parent_coin_id;
                    $closeOrder->trade_coin_id = $coinPairDetails->child_coin_id;
                    $closeOrder->price = $orderPrice;
                    $closeOrder->leverage = $leverage_amount;
                    $closeOrder->margin_mode = $margin_mode;
                    $closeOrder->trade_type = FUTURE_TRADE_TYPE_STOP_MARKET_LIMIT_CLOSE;
                    $closeOrder->is_position = FUTURE_TRADE_STOP_MARKET_LIMIT_POSITION;
                    $closeOrder->order_type = $order_type;
                    $closeOrder->stop_price = $request->stop_price;
                    $closeOrder->save();

                    return responseData(true, __('Close Order is Placed Successfully!'));
                } else {
                    return responseData(false, __('Stop Price must be greater than market price!'));
                }
            } else {
                return responseData(false, __('You have no order in position!'));
            }
        } catch (\Exception $e) {
            storeException('getFutureTradeSocketData', __('Something went wrong!'));
            return responseData(true, __('Something went wrong!'));
        }
    }

    public function closeMarketAndLimitLongShortOrder($user, $coinPairDetails, $request)
    {
        try {
            $orderList = FutureTradeLongShort::where('user_id', $user->id)
                ->whereNull('parent_id')
                ->where('side', $request->side)
                ->where('base_coin_id', $coinPairDetails->parent_coin_id)
                ->where('trade_coin_id', $coinPairDetails->child_coin_id)
                ->where('is_position', STATUS_ACTIVE)
                ->where('status', STATUS_INACTIVE)
                ->get();


            if ($orderList->count() > 0) {
                $order_type = $request->order_type;
                $amount = $request->amount;
                $marketPrice = $coinPairDetails->price;
                $orderPrice = $marketPrice;
                $amount_type = $request->amount_type;
                $leverage_amount = $request->leverage_amount;


                if ($order_type == LIMIT_ORDER || $order_type == STOP_LIMIT_ORDER) {
                    $orderPrice = $request->price;
                }


                $tradeCoinAmount = ($amount_type == AMOUNT_TYPE_TRADE) ? $amount : bcdivx($amount, $orderPrice, 8);

                $requestAmount = $tradeCoinAmount;

                if ($request->order_type == LIMIT_ORDER) {
                    if ($request->side == TRADE_TYPE_BUY && $request->price > $marketPrice) {
                        return responseData(false, __('Price can not be greater than market price for closing long order!'));
                    }

                    if ($request->side == TRADE_TYPE_SELL && $request->price < $marketPrice) {
                        return responseData(false, __('Price can not be less than market price for closing long order!'));
                    }
                }

                return $this->closeOrderInPosition($coinPairDetails, $orderList, $requestAmount, $orderPrice, $leverage_amount);
            } else {
                return responseData(false, __('You have no order in position!'));
            }
        } catch (\Exception $e) {
            storeException('getFutureTradeSocketData', __('Something went wrong!'));
            return responseData(true, __('Something went wrong!'));
        }
    }

    public function closeOrderInPosition($coinPairDetails, $orderList, $requestAmount, $orderPrice, $leverage_amount)
    {
        try {
            foreach ($orderList as $orderDetails) {
                $isBreak = false;

                $executableOrder = FutureTradeLongShort::where('id', $orderDetails->id)
                    ->whereNull('parent_id')
                    ->where('is_position', STATUS_ACTIVE)
                    ->where('status', STATUS_INACTIVE)
                    ->first();

                if (isset($executableOrder) && bccompx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount) == 1) {
                    $availableAmount = bcsubx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount, 8);

                    if ($requestAmount < $availableAmount) {
                        $closeAmount = $requestAmount;
                        $executedAmount = bcaddx($executableOrder->executed_amount, $requestAmount, 8);
                        $isPosition = STATUS_ACTIVE;

                        $isBreak = true;
                    } elseif ($requestAmount == $availableAmount) {
                        $closeAmount = $requestAmount;
                        $executedAmount = bcaddx($executableOrder->executed_amount, $requestAmount, 8);
                        $isPosition = STATUS_INACTIVE;

                        $isBreak = true;
                    } else {

                        $closeAmount = bcsubx($requestAmount, $availableAmount, 8);
                        $executedAmount = bcaddx($executableOrder->executed_amount, $availableAmount, 8);
                        $isPosition = STATUS_INACTIVE;

                        $requestAmount = bcsubx($requestAmount, $availableAmount, 8);
                    }

                    $baseCoinAmount = bcmulx($closeAmount, $orderPrice, 8);
                    $sendAbleBaseCoinAmount = bcdivx($baseCoinAmount, $executableOrder->leverage, 8);

                    $fundingFees = bcdivx(bcmulx($sendAbleBaseCoinAmount, $coinPairDetails->leverage_fee, 8), 100, 8);

                    $futureTradeComissionData = futureTradeFees($sendAbleBaseCoinAmount);

                    if ($executableOrder->side == TRADE_TYPE_BUY) {
                        $comissionFees = bcmulx(bcmulx($sendAbleBaseCoinAmount, $futureTradeComissionData['maker_fees'], 8), 0.01, 8);
                    } else {
                        $comissionFees = bcmulx(bcmulx($sendAbleBaseCoinAmount, $futureTradeComissionData['taker_fees'], 8), 0.01, 8);
                    }

                    $pnl = calculateFutureTradeProfitLoss($executableOrder->side, $executableOrder->entry_price, $closeAmount, $orderPrice);

                    $closeOrder = new FutureTradeLongShort;
                    $closeOrder->uid = generateUID();
                    $closeOrder->parent_id = $executableOrder->id;
                    $closeOrder->side = $executableOrder->side;
                    $closeOrder->amount_in_base_coin = $baseCoinAmount;
                    $closeOrder->amount_in_trade_coin = $closeAmount;
                    $closeOrder->user_id = $orderDetails->user_id;
                    $closeOrder->base_coin_id = $executableOrder->base_coin_id;
                    $closeOrder->trade_coin_id = $executableOrder->trade_coin_id;
                    $closeOrder->entry_price = $executableOrder->entry_price;
                    $closeOrder->margin = $executableOrder->margin;
                    $closeOrder->price = $orderPrice;
                    $closeOrder->fees = $fundingFees;
                    $closeOrder->comission = $comissionFees;
                    $closeOrder->leverage = $leverage_amount;
                    $closeOrder->margin_mode = $orderDetails->margin_mode;
                    $closeOrder->trade_type = FUTURE_TRADE_TYPE_CLOSE;
                    $closeOrder->is_market = $orderDetails->is_market;
                    $closeOrder->order_type = $orderDetails->order_type;
                    $closeOrder->pnl = $pnl;
                    $closeOrder->save();

                    $userReturnAmount = bcsubx(bcaddx($sendAbleBaseCoinAmount, $pnl), bcaddx($fundingFees, $comissionFees));
                    $userWallet = FutureWallet::where('user_id', $executableOrder->user_id)
                        ->where('coin_id', $executableOrder->base_coin_id)
                        ->first();
                    $userWallet->increment('balance', $userReturnAmount);

                    $executableChildOrder = FutureTradeLongShort::where('parent_id', $executableOrder->id)
                        ->update(['is_position' => $isPosition]);

                    $executableOrder->is_position = $isPosition;
                    $executableOrder->executed_amount = $executedAmount;
                    $executableOrder->save();



                    createFutureTradeTransaction(
                        $executableOrder->user_id,
                        $userWallet->id,
                        FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL,
                        $pnl,
                        $userWallet->coin_type,
                        coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                        $coinPairDetails->id,
                        $closeOrder->id
                    );

                    createFutureTradeTransaction(
                        $executableOrder->user_id,
                        $userWallet->id,
                        FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION,
                        $comissionFees,
                        $userWallet->coin_type,
                        coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                        $coinPairDetails->id,
                        $closeOrder->id
                    );

                    createFutureTradeTransaction(
                        $executableOrder->user_id,
                        $userWallet->id,
                        FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES,
                        $fundingFees,
                        $userWallet->coin_type,
                        coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                        $coinPairDetails->id,
                        $closeOrder->id
                    );

                    if ($isBreak) {
                        break;
                    }
                }
            }
            $response = responseData(true, __('Close Order is Placed Successfully!'));
        } catch (\Exception $e) {
            storeException('getFutureTradeSocketData', __('Something went wrong!'));
            $response = responseData(true, __('Something went wrong!'));
        }

        return $response;
    }

    public function getFutureTradeUserWalletDetails($userId, $coinId)
    {
        $walletDetails = FutureWallet::where('user_id', $userId)
            ->where('coin_id', $coinId)
            ->first();

        return responseData(true, __('User Wallet Details'), $walletDetails);
    }

    public function getLongShortOrderHistory($request)
    {
        if (!isset($request->base_coin_id) || !isset($request->trade_coin_id)) {
            return responseData(false, __('Base Coin Id and Trade Coin Id is required!'));
        }

        $orderList = FutureTradeLongShort::when(isset($request->side), function ($query) use ($request) {
            $query->where('side', $request->side);
        })
            ->where('base_coin_id', $request->base_coin_id)
            ->where('trade_coin_id', $request->trade_coin_id)
            ->orderBy('id', 'desc')
            ->get();

        $orderList->map(function ($query) {
            $query['profit_loss_calculation'] = calculatePositionData($query->id);
        });

        $response = responseData(true, __('Long Short order history'), $orderList);

        return $response;
    }

    public function getLongShortTransactionHistory($request)
    {
        if (!isset($request->coin_pair_id)) {
            return responseData(false, __('Coin Pair Id is required!'));
        }

        $user = auth()->user();

        $transactionList = FutureTradeTransactionHistory::where('user_id', $user->id)
            ->where('coin_pair_id', $request->coin_pair_id)
            ->orderBy('id', 'desc')
            ->get();

        $response = responseData(true, __('Long Short transaction history'), $transactionList);

        return $response;
    }

    public function getLongShortTradeHistory($request)
    {
        if (!isset($request->base_coin_id) || !isset($request->trade_coin_id)) {
            return responseData(false, __('Base Coin Id and Trade Coin Id is required!'));
        }

        $orderList = FutureTradeLongShort::when(isset($request->side), function ($query) use ($request) {
            $query->where('side', $request->side);
        })
            ->where('base_coin_id', $request->base_coin_id)
            ->where('trade_coin_id', $request->trade_coin_id)
            ->whereNotNull('parent_id')
            ->where('is_position', 0)
            ->where(function ($query) {
                $query->orWhere('take_profit_price', 0)
                    ->orWhere('stop_loss_price', 0);
            })
            ->get();

        $orderList->map(function ($query) {
            $query['profit_loss_calculation'] = calculatePositionData($query->id);
        });

        $response = responseData(true, __('Long Short trade history'), $orderList);

        return $response;
    }

    public function closeLongShortAllOrders($request)
    {
        try {
            if (!isset($request->coin_pair_id)) {
                return responseData(false, __('Coin pair id is required!'));
            }

            $closeOrderListData = $request->data;

            if (count($closeOrderListData)) {
                $coinPairDetails = CoinPair::where('id', $request->coin_pair_id)
                    ->where('status', STATUS_ACTIVE)
                    ->where('enable_future_trade', STATUS_ACTIVE)
                    ->first();

                if (!isset($coinPairDetails)) {
                    return responseData(false, __('Coin Pair details not found!'));
                }

                $marketPrice = $coinPairDetails->price;

                foreach ($closeOrderListData as $key => $closeOrderDataDetails) {
                    if (!isset($closeOrderDataDetails['order_id'])) {
                        return responseData(false, __('Order Id is missing for position:') . $key);
                    }

                    if (!isset($closeOrderDataDetails['order_type'])) {
                        return responseData(false, __('Order type is missing for order ID:') . $closeOrderDataDetails['order_id']);
                    }

                    if (!isset($closeOrderDataDetails['side'])) {
                        return responseData(false, __('Side is missing for order ID:') . $closeOrderDataDetails['order_id']);
                    }

                    if ($closeOrderDataDetails['order_type'] == LIMIT_ORDER && !isset($closeOrderDataDetails['price'])) {
                        return responseData(false, __('Enter price for order id:') . $closeOrderDataDetails['order_id']);
                    }

                    if ($closeOrderDataDetails['order_type'] == LIMIT_ORDER) {
                        if ($closeOrderDataDetails['side'] == TRADE_TYPE_BUY && $closeOrderDataDetails['price'] > $marketPrice) {
                            return responseData(false, __('Price can not be greater than market price for closing long order, Order ID:') . $closeOrderDataDetails['order_id']);
                        }

                        if ($closeOrderDataDetails['side'] == TRADE_TYPE_SELL && $closeOrderDataDetails['price'] < $marketPrice) {
                            return responseData(false, __('Price can not be less than market price for closing long order, Order ID:') . $closeOrderDataDetails['order_id']);
                        }
                    }
                }

                foreach ($closeOrderListData as $closeOrderDataDetails) {
                    $executableOrder = FutureTradeLongShort::where('id', $closeOrderDataDetails['order_id'])
                        ->whereNull('parent_id')
                        ->where('is_position', STATUS_ACTIVE)
                        ->where('status', STATUS_INACTIVE)
                        ->first();


                    if (isset($executableOrder) && bccompx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount) == 1) {

                        $closeAmount = bcsubx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount, 8);

                        if ($closeOrderDataDetails['order_type'] == LIMIT_ORDER) {
                            $orderPrice = $closeOrderDataDetails['price'];
                        } else {
                            $orderPrice = $marketPrice;
                        }

                        $baseCoinAmount = bcmulx($closeAmount, $orderPrice, 8);

                        $sendAbleBaseCoinAmount = bcdivx($baseCoinAmount, $executableOrder->leverage, 8);

                        $fundingFees = bcdivx(bcmulx($sendAbleBaseCoinAmount, $coinPairDetails->leverage_fee, 8), 100, 8);

                        $futureTradeComissionData = futureTradeFees($sendAbleBaseCoinAmount);

                        if ($executableOrder->side == TRADE_TYPE_BUY) {
                            $comissionFees = bcmulx(bcmulx($sendAbleBaseCoinAmount, $futureTradeComissionData['maker_fees'], 8), 0.01, 8);
                        } else {
                            $comissionFees = bcmulx(bcmulx($sendAbleBaseCoinAmount, $futureTradeComissionData['taker_fees'], 8), 0.01, 8);
                        }

                        $pnl = calculateFutureTradeProfitLoss($executableOrder->side, $executableOrder->entry_price, $closeAmount, $orderPrice);

                        $closeOrder = new FutureTradeLongShort;
                        $closeOrder->uid = generateUID();
                        $closeOrder->parent_id = $executableOrder->id;
                        $closeOrder->side = $executableOrder->side;
                        $closeOrder->exist_price = $marketPrice;
                        $closeOrder->amount_in_base_coin = $baseCoinAmount;
                        $closeOrder->amount_in_trade_coin = $closeAmount;
                        $closeOrder->user_id = $executableOrder->user_id;
                        $closeOrder->base_coin_id = $executableOrder->base_coin_id;
                        $closeOrder->trade_coin_id = $executableOrder->trade_coin_id;
                        $closeOrder->entry_price = $executableOrder->entry_price;
                        $closeOrder->margin = $executableOrder->margin;
                        $closeOrder->price = $orderPrice;
                        $closeOrder->fees = $fundingFees;
                        $closeOrder->comission = $comissionFees;
                        $closeOrder->leverage = $executableOrder->leverage;
                        $closeOrder->margin_mode = $executableOrder->margin_mode;
                        $closeOrder->trade_type = FUTURE_TRADE_TYPE_CLOSE;
                        $closeOrder->is_market = ($closeOrderDataDetails['order_type'] == MARKET_ORDER) ? 1 : 0;
                        $closeOrder->pnl = $pnl;
                        $closeOrder->save();

                        $userReturnAmount = bcsubx(bcaddx($sendAbleBaseCoinAmount, $pnl, 8), bcaddx($fundingFees, $comissionFees, 8), 8);

                        $userWallet = FutureWallet::where('user_id', $executableOrder->user_id)
                            ->where('coin_id', $executableOrder->base_coin_id)
                            ->first();
                        $userWallet->increment('balance', $userReturnAmount);

                        FutureTradeLongShort::where('parent_id', $executableOrder->id)
                            ->update(['is_position' => 0, 'status' => STATUS_DELETED]);

                        $executableOrder->is_position = 0;
                        $executableOrder->executed_amount = $closeAmount;
                        $executableOrder->save();



                        createFutureTradeTransaction(
                            $executableOrder->user_id,
                            $userWallet->id,
                            FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL,
                            $pnl,
                            $userWallet->coin_type,
                            coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                            $coinPairDetails->id,
                            $closeOrder->id
                        );

                        createFutureTradeTransaction(
                            $executableOrder->user_id,
                            $userWallet->id,
                            FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION,
                            $comissionFees,
                            $userWallet->coin_type,
                            coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                            $coinPairDetails->id,
                            $closeOrder->id
                        );

                        createFutureTradeTransaction(
                            $executableOrder->user_id,
                            $userWallet->id,
                            FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES,
                            $fundingFees,
                            $userWallet->coin_type,
                            coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                            $coinPairDetails->id,
                            $closeOrder->id
                        );
                    }
                }

                return responseData(true, __('Closed all order successfully!'));
            } else {
                return responseData(false, __('Data is empty!'));
            }
        } catch (\Exception $e) {
            storeException('closeLongShortAllOrders catch', $e->getMessage());
            storeException('closeLongShortAllOrders', __('Something went wrong!'));
            return responseData(true, __('Something went wrong!'));
        }
    }
}
