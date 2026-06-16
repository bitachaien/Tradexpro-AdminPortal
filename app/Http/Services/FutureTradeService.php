<?php

namespace App\Http\Services;

use App\Model\Wallet;
use App\Model\CoinPair;
use App\Model\FutureWallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Model\FutureTradeLongShort;
use App\Model\FutureTradeTransactionHistory;
use App\Model\FutureTradeBalanceTransferHistory;

class FutureTradeService
{

    public function getWalletList($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;
        $user = auth()->user();

        $type = $request->type ?? SPOT_TRADE;
        $wallet_list = null;
        if ($type == SPOT_TRADE) {
            $wallet_list = Wallet::with("coin")
                ->where('user_id', $user->id)
                ->where('status', STATUS_ACTIVE)
                ->when(isset($request->search), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('balance', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('name', 'LIKE', '%' . $request->search . '%');
                    });
                })
                ->paginate($limit, ['*'], 'page', $offset);
            $message = __('Spot trade active wallet list');
        } else {
            $wallet_list = FutureWallet::with("coin")
                ->where('user_id', $user->id)
                ->where('status', STATUS_ACTIVE)
                ->when(isset($request->search), function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->orWhere('balance', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('wallet_name', 'LIKE', '%' . $request->search . '%');
                    });
                })
                ->paginate($limit, ['*'], 'page', $offset);

            $message = __('Future trade active wallet list');
        }
        $wallet_list->map(function ($wallet) {
            $wallet->coin_icon = isset($wallet?->coin?->coin_icon) ? asset(IMG_ICON_PATH . $wallet?->coin?->coin_icon) : null;
        });
        $response = responseData(true, $message, $wallet_list);

        return $response;
    }

    public function balanceTransfer($request)
    {
        $user = auth()->user();

        try {
            if ($request->transfer_from == SPOT_TRADE) {
                $spotWallet = Wallet::where('user_id', $user->id)->where('status', STATUS_ACTIVE)->where('coin_type', $request->coin_type)->first();

                if (isset($spotWallet)) {
                    if ($spotWallet->balance >= $request->amount) {
                        $futureWallet = FutureWallet::where('user_id', $user->id)->where('status', STATUS_ACTIVE)->where('coin_type', $request->coin_type)->first();

                        if (isset($futureWallet)) {
                            $spotWallet->balance = $spotWallet->balance - $request->amount;
                            $spotWallet->save();

                            $futureWallet->balance = $futureWallet->balance + $request->amount;
                            $futureWallet->save();
                        } else {
                            $response = responseData(false, __('Future trade Wallet is not found!'));
                            return $response;
                        }
                    } else {
                        $response = responseData(false, __('You have no sufficent Balance!'));
                        return $response;
                    }
                } else {
                    $response = responseData(false, __('Spot trade Wallet is not found!'));
                    return $response;
                }
            } else {
                $futureWallet = FutureWallet::where('user_id', $user->id)->where('status', STATUS_ACTIVE)->where('coin_type', $request->coin_type)->first();

                if (isset($futureWallet)) {
                    if ($futureWallet->balance >= $request->amount) {
                        $spotWallet = Wallet::where('user_id', $user->id)->where('status', STATUS_ACTIVE)->where('coin_type', $request->coin_type)->first();

                        if (isset($spotWallet)) {
                            $futureWallet->balance = $futureWallet->balance - $request->amount;
                            $futureWallet->save();

                            $spotWallet->balance = $spotWallet->balance + $request->amount;
                            $spotWallet->save();
                        } else {
                            $response = responseData(false, __('Spot trade Wallet is not found!'));
                            return $response;
                        }
                    } else {
                        $response = responseData(false, __('You have no sufficent Balance!'));
                        return $response;
                    }
                } else {
                    $response = responseData(false, __('Future trade Wallet is not found!'));
                    return $response;
                }
            }

            $transferHistory = new FutureTradeBalanceTransferHistory;
            $transferHistory->user_id = $user->id;
            $transferHistory->spot_wallet_id = $spotWallet->id;
            $transferHistory->future_wallet_id = $futureWallet->id;
            $transferHistory->amount = $request->amount;
            $transferHistory->transfer_from = $request->transfer_from;
            $transferHistory->save();

            $response = responseData(true, __('Balance is transfered successfully!'));
        } catch (\Exception $e) {
            storeException('balanceTransfer', $e->getMessage());
            $response = responseData(false, __('Something went wrong!'));
        }

        return $response;
    }

    public function walletTransferHistory($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;
        $user = auth()->user();
        $type = $request->type;

        $transferHistory = FutureTradeBalanceTransferHistory::with(['user', 'spot_wallet', 'future_wallet'])->where('user_id', $user->id)
            ->when(isset($type), function ($query) use ($type) {
                $query->where('transfer_from', $type);
            })->latest()->paginate($limit, ['*'], 'page', $offset);


        $response = responseData(true, __('Wallet transfer History!'), $transferHistory);
        return $response;
    }

    public function coinPairList($request)
    {
        $limit = isset($request->limit) ? $request->limit : 25;
        $offset = isset($request->page) ? $request->page : 1;

        $coinPairList = CoinPair::where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->latest()->paginate($limit, ['*'], 'page', $offset);

        $response = responseData(true, __('Coin Pair List!'), $coinPairList);
        return $response;
    }
    public function prePlaceOrderData($request)
    {
        $user = auth()->user();

        $coinPairDetails = CoinPair::with(['parent_coin', 'child_coin'])
            ->where('id', $request->coin_pair_id)
            ->where('status', STATUS_ACTIVE)
            ->where('enable_future_trade', STATUS_ACTIVE)
            ->first();

        if (isset($coinPairDetails)) {
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
                $orderPrice = $request->price;
                $amount_type = $request->amount_type;
                $leverage_amount = $request->leverage_amount;
                $userWalletBalance = $userWalletDetails->balance;
                if ($order_type == MARKET_ORDER) {
                    $orderPrice = $marketPrice;
                }

                $maxBaseAmount = $userWalletBalance * $request->leverage_amount;

                // cost calculation start
                $calculateCost = calculateCostForFutureTrade($coinPairDetails, $order_type, $amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);
                $maxCost = calculateMaxCostForFutureTrade($coinPairDetails, $order_type, $maxBaseAmount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);                // max calculation end

                if ($amount_type == AMOUNT_TYPE_BASE) {
                    if ($request->amount > $maxCost['totalCostLong']) {
                        $response = responseData(false, __('Insufficient margin amount'));

                        return $response;
                    }
                } else {
                    if ($request->amount > $maxCost['totalCostLongTrade']) {
                        $response = responseData(false, __('Insufficient margin amount'));

                        return $response;
                    }
                }


                $data['wallet_balance'] = $userWalletBalance;

                $data['long_cost_funding_fees'] = $calculateCost['fundingFeesLong'];
                $data['long_cost_commission_fees'] = $calculateCost['commissionFeesLong'];
                $data['long_cost'] = $calculateCost['totalCostLong'];

                $data['short_cost_funding_fees'] = $calculateCost['fundingFeesShort'];
                $data['short_cost_commission_fees'] = $calculateCost['commissionFeesShort'];
                $data['short_cost'] = $calculateCost['totalCostShort'];

                $data['max_size_open_long_base_funding_fees'] = $maxCost['fundingFeesLong'];
                $data['max_size_open_long_base_commission_fees'] = $maxCost['commissionFeesLong'];
                $data['max_size_open_long_base'] = $maxCost['totalCostLong'];

                $data['max_size_open_short_base_funding_fees'] = $maxCost['fundingFeesShort'];
                $data['max_size_open_short_base_commission_fees'] = $maxCost['commissionFeesLong'];
                $data['max_size_open_short_base'] = $maxCost['totalCostLong'];

                $data['max_size_open_long_trade'] = $maxCost['totalCostLongTrade'];
                $data['max_size_open_short_trade'] = $maxCost['totalCostShortTrade'];

                $response = responseData(true, __('Pre Place Order Data'), $data);
            } else {
                $response = responseData(false, 'Wallet is not found!');
            }
        } else {
            $response = responseData(false, 'Coin does not exist!');
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

    public function getFutureTradeSocketData($user_id, $base_coin_id, $trade_coin_id)
    {
        // return $orderDetails->child_coin_id;
        try {
            $coinPairDetails = CoinPair::where('parent_coin_id', $base_coin_id)
                ->where('child_coin_id', $trade_coin_id)
                ->first();

            if (isset($coinPairDetails)) {
                $positionOrderList = FutureTradeLongShort::where('user_id', $user_id)
                    ->whereNull('parent_id')
                    ->where('base_coin_id', $base_coin_id)
                    ->where('trade_coin_id', $trade_coin_id)
                    ->where('is_position', STATUS_ACTIVE)
                    ->where('status', STATUS_INACTIVE)
                    ->orderBy('id', 'desc')
                    ->latest()
                    ->limit(20)
                    ->get();

                $positionOrderList->map(function ($query) {
                    $query['profit_loss_calculation'] = calculatePositionData($query->id);
                });

                // $openOrderList = FutureTradeLongShort::where('user_id', $user_id)
                //                     ->where('base_coin_id', $base_coin_id)
                //                     ->where('trade_coin_id', $trade_coin_id)
                //                     ->where(function($query){
                //                         $query->orWhere('take_profit_price','<>', 0)
                //                         ->orWhere('stop_loss_price','<>', 0);
                //                     })
                //                     ->where('status', STATUS_INACTIVE)
                //                     ->get();
                $openOrderList = FutureTradeLongShort::where('user_id', $user_id)
                    ->where('base_coin_id', $base_coin_id)
                    ->where('trade_coin_id', $trade_coin_id)
                    ->where('is_position', '<>', FUTURE_TRADE_IS_POSITION)
                    ->where('status', STATUS_INACTIVE)
                    ->orderBy('id', 'desc')
                    ->latest()
                    ->limit(20)
                    ->get();

                $openOrderList->map(function ($query) {
                    $query['profit_loss_calculation'] = calculatePositionData($query->id);
                });

                $orderHistoryList = FutureTradeLongShort::where('base_coin_id', $base_coin_id)
                    ->where('trade_coin_id', $trade_coin_id)
                    ->orderBy('id', 'desc')
                    ->latest()
                    ->limit(20)
                    ->get();

                $orderHistoryList->map(function ($query) {
                    $query['profit_loss_calculation'] = calculatePositionData($query->id);
                });

                $tradeHistoryList = FutureTradeLongShort::where('base_coin_id', $base_coin_id)
                    ->where('trade_coin_id', $trade_coin_id)
                    ->whereNotNull('parent_id')
                    ->where('is_position', 0)
                    ->where(function ($query) {
                        $query->orWhere('take_profit_price', 0)
                            ->orWhere('stop_loss_price', 0);
                    })
                    ->latest()
                    ->limit(20)
                    ->orderBy('id', 'desc')
                    ->get();

                $tradeHistoryList->map(function ($query) {
                    $query['profit_loss_calculation'] = calculatePositionData($query->id);
                });

                $transactionList = FutureTradeTransactionHistory::where('user_id', $user_id)
                    ->where('coin_pair_id', $coinPairDetails->coin_pair_id)
                    ->orderBy('id', 'desc')
                    ->latest()
                    ->limit(20)
                    ->get();

                $data['position_order_list'] = $positionOrderList;
                $data['open_order_list'] = $openOrderList;
                $data['order_history_list'] = $orderHistoryList;
                $data['trade_history_list'] = $tradeHistoryList;
                $data['transaction_list'] = $transactionList;

                return $data;
            } else {
                storeException('getFutureTradeSocketData', __('Coin Pair is not found!'));
            }
        } catch (\Exception $e) {
            storeException('getFutureTradeSocketData', __('Something went wrong!'));
        }
    }

    public function getFutureTradeExchangeMarketDetailsApp($request)
    {
        // $limit = $request->limit ?? 10;
        $type = $request->type ?? 'assets';

        $coins = CoinPair::select(
            'coin_pairs.id as coin_pair_id',
            'parent_coin_id',
            'child_coin_id',
            DB::raw("visualNumberFormat(price) as last_price"),
            DB::raw("visualNumberFormat(0) as balance"),
            'change as price_change',
            'volume',
            'high',
            'low',
            'child_coin.coin_type as child_coin_name',
            'parent_coin.coin_type as parent_coin_name',
            'child_coin.name as child_full_name',
            'parent_coin.name as parent_full_name',
            'child_coin.coin_icon'
        )
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->where('coin_pairs.status', STATUS_ACTIVE)
            ->where('coin_pairs.enable_future_trade', STATUS_ACTIVE)
            ->when($type == 'new', function ($query) {
                $query->latest('coin_pairs.created_at');
            })
            ->when($type == 'hour', function ($query) {
                $query->where('coin_pairs.updated_at', '>=', Carbon::now()->subDay());
            })
            ->orderBy('coin_pairs.volume', 'desc')
            ->get();

        $coins->map(function ($query) {
            if (isset($query->coin_icon)) {
                $query->coin_icon = show_image_path($query->coin_icon, 'coin/');
            }
        });
        $data['coins'] = $coins;
        $data['getHighestVolumePair'] = getHighestVolumePair();
        $data['profit_loss_by_coin_pair'] = getHighLowPNLByCoinPairGroup();

        return responseData(true, __("Market details get successfully"), $data);
    }

    public function getFutureTradeExchangeMarketDetailsWebsocketData()
    {
        $type = 'assets';
        $coins = CoinPair::select(
            'coin_pairs.id as coin_pair_id',
            'parent_coin_id',
            'child_coin_id',
            DB::raw("visualNumberFormat(price) as last_price"),
            DB::raw("visualNumberFormat(0) as balance"),
            'change as price_change',
            'volume',
            'high',
            'low',
            'child_coin.coin_type as child_coin_name',
            'parent_coin.coin_type as parent_coin_name',
            'child_coin.name as child_full_name',
            'parent_coin.name as parent_full_name',
            'child_coin.coin_icon'
        )
            ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
            ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
            ->where('coin_pairs.status', STATUS_ACTIVE)
            ->where('coin_pairs.enable_future_trade', STATUS_ACTIVE)
            ->when($type == 'new', function ($query) {
                $query->latest('coin_pairs.created_at');
            })
            ->when($type == 'hour', function ($query) {
                $query->where('coin_pairs.updated_at', '>=', Carbon::now()->subDay());
            })
            ->orderBy('coin_pairs.volume', 'desc')
            ->get();

        $coins->map(function ($query) {
            if (isset($query->coin_icon)) {
                $query->coin_icon = show_image_path($query->coin_icon, 'coin/');
            }
        });
        $data['coins'] = $coins;
        $data['getHighestVolumePair'] = getHighestVolumePair();
        $data['profit_loss_by_coin_pair'] = getHighLowPNLByCoinPairGroup();

        return $data;
    }

    public function autoCloseLongShortOrder($openOrderList, $coinPairDetails)
    {
        try {
            if ($openOrderList->count() > 0) {
                $marketPrice = $coinPairDetails->price;

                foreach ($openOrderList as $openOrderDetails) {
                    $takeProfitOrder = FutureTradeLongShort::where('parent_id', $openOrderDetails->id)->where('take_profit_price', '>', 0)->first();
                    $stopLossOrder = FutureTradeLongShort::where('parent_id', $openOrderDetails->id)->where('stop_loss_price', '>', 0)->first();

                    $has_executable_order = false;
                    $has_take_profit_order = false;
                    $has_stop_loss_order = false;

                    if ($openOrderDetails->side == TRADE_TYPE_BUY) {
                        if (
                            isset($takeProfitOrder) &&
                            $takeProfitOrder->take_profit_price > 0 &&
                            $takeProfitOrder->take_profit_price <= $marketPrice
                        ) {
                            $has_executable_order = true;
                            $has_take_profit_order = true;
                        } elseif (
                            isset($stopLossOrder) &&
                            $stopLossOrder->stop_loss_price > 0 &&
                            $stopLossOrder->stop_loss_price >= $marketPrice
                        ) {
                            $has_executable_order = true;
                            $has_stop_loss_order = true;
                        } elseif ($openOrderDetails->liquidation_price >= $marketPrice) {
                            $has_executable_order = true;
                        }
                    } else {

                        if (
                            isset($takeProfitOrder) &&
                            $takeProfitOrder->take_profit_price > 0 &&
                            $takeProfitOrder->take_profit_price >= $marketPrice
                        ) {
                            $has_executable_order = true;
                            $has_take_profit_order = true;
                        } elseif (
                            isset($stopLossOrder) &&
                            $stopLossOrder->stop_loss_price > 0 &&
                            $stopLossOrder->stop_loss_price <= $marketPrice
                        ) {
                            $has_executable_order = true;
                            $has_stop_loss_order = true;
                        } elseif ($openOrderDetails->liquidation_price <= $marketPrice) {
                            $has_executable_order = true;
                        }
                    }

                    if ($has_executable_order) {
                        $executableOrder = FutureTradeLongShort::where('id', $openOrderDetails->id)
                            ->whereNull('parent_id')
                            ->where('is_position', STATUS_ACTIVE)
                            ->where('status', STATUS_INACTIVE)
                            ->first();


                        if (isset($executableOrder) && bccompx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount) == 1) {
                            $closeAmount = bcsubx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount, 8);

                            $orderPrice = $marketPrice;

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

                            if ($has_take_profit_order == false && $has_stop_loss_order == false) {

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
                                $closeOrder->is_market = 1;
                                $closeOrder->pnl = $pnl;
                                $closeOrder->save();
                            }

                            if (isset($takeProfitOrder)) {
                                if ($has_take_profit_order) {
                                    $takeProfitOrder->exist_price = $marketPrice;
                                    $takeProfitOrder->pnl = $pnl;
                                    $takeProfitOrder->is_position = 0;
                                    $takeProfitOrder->status = STATUS_ACTIVE;
                                } else {
                                    $takeProfitOrder->is_position = 0;
                                    $takeProfitOrder->status = STATUS_DELETED;
                                }

                                $takeProfitOrder->save();
                            }

                            if (isset($stopLossOrder)) {
                                if ($has_stop_loss_order) {
                                    $stopLossOrder->exist_price = $marketPrice;
                                    $stopLossOrder->pnl = $pnl;
                                    $stopLossOrder->is_position = 0;
                                    $stopLossOrder->status = STATUS_ACTIVE;
                                } else {
                                    $stopLossOrder->is_position = 0;
                                    $stopLossOrder->status = STATUS_DELETED;
                                }

                                $stopLossOrder->save();
                            }

                            $userReturnAmount = bcsubx(bcaddx($sendAbleBaseCoinAmount, $pnl), bcaddx($fundingFees, $comissionFees));
                            $userWallet = FutureWallet::where('user_id', $executableOrder->user_id)
                                ->where('coin_id', $executableOrder->base_coin_id)
                                ->first();
                            $userWallet->increment('balance', $userReturnAmount);

                            $executableOrder->exist_price = $marketPrice;
                            $executableOrder->is_position = 0;
                            $executableOrder->executed_amount = $closeAmount;
                            $executableOrder->save();

                            $closeOrderID = isset($closeOrder) ? $closeOrder->id : null;

                            createFutureTradeTransaction(
                                $executableOrder->user_id,
                                $userWallet->id,
                                FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL,
                                $pnl,
                                $userWallet->coin_type,
                                coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                                $coinPairDetails->id,
                                $closeOrderID
                            );

                            createFutureTradeTransaction(
                                $executableOrder->user_id,
                                $userWallet->id,
                                FUTURE_TRADE_TRANSACTION_TYPE_COMMISSION,
                                $comissionFees,
                                $userWallet->coin_type,
                                coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                                $coinPairDetails->id,
                                $closeOrderID
                            );

                            createFutureTradeTransaction(
                                $executableOrder->user_id,
                                $userWallet->id,
                                FUTURE_TRADE_TRANSACTION_TYPE_FUNDING_FEES,
                                $fundingFees,
                                $userWallet->coin_type,
                                coinPairSymbol($executableOrder->base_coin_id, $executableOrder->trade_coin_id),
                                $coinPairDetails->id,
                                $closeOrderID
                            );

                            // storeException('Executed Close Long Short Order Details', $executableOrder);
                        }
                    }
                }
                // storeException('autoCloseLongShortOrder',__('End Process!'));
                return responseData(true, __('Executed Long Short order successfully!'));
            } else {
                // storeException('autoCloseLongShortOrder',__('Open order list is 0!'));
                return responseData(false, __('Open order list is 0!'));
            }
        } catch (\Exception $e) {
            storeException('autoCloseLongShortOrder', __('Something went wrong!'));
            storeException('autoCloseLongShortOrder Catch Error', $e->getMessage());
        }
    }

    public function holdOrderMakePosition($coinPairDetails)
    {
        $orderList = FutureTradeLongShort::whereNull('parent_id')
            ->where('base_coin_id', $coinPairDetails->parent_coin_id)
            ->where('trade_coin_id', $coinPairDetails->child_coin_id)
            ->where('is_position', FUTURE_TRADE_HOLD_POSITION)
            ->get();

        if ($orderList->count() > 0) {
            foreach ($orderList as $orderDetails) {
                $isPosition = FUTURE_TRADE_HOLD_POSITION;

                if ($orderDetails->margin_mode == MARGIN_MODE_CROSS) {
                    if (
                        $orderDetails->side == TRADE_TYPE_BUY &&
                        $orderDetails->avg_close_price > $coinPairDetails->price
                    ) {
                        $isPosition = FUTURE_TRADE_IS_POSITION;
                    }

                    if (
                        $orderDetails->side == TRADE_TYPE_SELL &&
                        $orderDetails < $coinPairDetails->price
                    ) {
                        $isPosition = FUTURE_TRADE_IS_POSITION;
                    }
                } else {
                    if (
                        $orderDetails->side == TRADE_TYPE_BUY &&
                        ($orderDetails->order_type == STOP_LIMIT_ORDER ||
                            $orderDetails->order_type == STOP_MARKET_ORDER) &&
                        $orderDetails->stop_price < $coinPairDetails->price
                    ) {
                        $isPosition = FUTURE_TRADE_IS_POSITION;
                    }

                    if (
                        $orderDetails->side == TRADE_TYPE_SELL &&
                        ($orderDetails->order_type == STOP_LIMIT_ORDER ||
                            $orderDetails->order_type == STOP_MARKET_ORDER) &&
                        $orderDetails->stop_price > $coinPairDetails->price
                    ) {
                        $isPosition = FUTURE_TRADE_IS_POSITION;
                    }
                }

                if ($isPosition == FUTURE_TRADE_IS_POSITION) {
                    FutureTradeLongShort::where('id', $orderDetails->id)->update(['is_position' => $isPosition]);
                }
            }
        }
    }

    public function autoCloseLongShortStopMarketLimitOrder($coinPairDetails)
    {
        $closeStopMarketLimitOrderList = FutureTradeLongShort::whereNull('parent_id')
            ->where('base_coin_id', $coinPairDetails->parent_coin_id)
            ->where('trade_coin_id', $coinPairDetails->child_coin_id)
            ->where('trade_type', FUTURE_TRADE_TYPE_STOP_MARKET_LIMIT_CLOSE)
            ->where('is_position', FUTURE_TRADE_STOP_MARKET_LIMIT_POSITION)
            ->where('status', STATUS_INACTIVE)
            ->get();

        if ($closeStopMarketLimitOrderList->count() > 0) {
            foreach ($closeStopMarketLimitOrderList as $closeOrderDetails) {
                $orderList = FutureTradeLongShort::where('user_id', $closeOrderDetails->user_id)
                    ->whereNull('parent_id')
                    ->where('order_type', $closeOrderDetails->order_type)
                    ->where('side', $closeOrderDetails->side)
                    ->where('base_coin_id', $coinPairDetails->parent_coin_id)
                    ->where('trade_coin_id', $coinPairDetails->child_coin_id)
                    ->where('trade_type', FUTURE_TRADE_TYPE_OPEN)
                    ->where('is_position', FUTURE_TRADE_IS_POSITION)
                    ->where('status', STATUS_INACTIVE)
                    ->get();
                if ($orderList->count() > 0) {
                    $requestAmount = $closeOrderDetails->amount_in_trade_coin;
                    $orderPrice = $closeOrderDetails->price;
                    $leverage_amount = $closeOrderDetails->leverage;
                    $response = $this->closeAllLongShortStopMarketLimitOrder($coinPairDetails, $orderList, $requestAmount, $orderPrice, $leverage_amount);

                    if ($response['success']) {
                        FutureTradeLongShort::where('id', $closeOrderDetails->id)->update(['status', STATUS_DELETED]);
                    }
                }
            }
        }
    }

    public function closeAllLongShortStopMarketLimitOrder($coinPairDetails, $orderList, $requestAmount, $orderPrice, $leverage_amount)
    {
        try {
            foreach ($orderList as $orderDetails) {
                $isBreak = false;

                $executableOrder = FutureTradeLongShort::where('id', $orderDetails->id)
                    ->whereNull('parent_id')
                    ->where('trade_type', FUTURE_TRADE_TYPE_OPEN)
                    ->where('is_position', FUTURE_TRADE_IS_POSITION)
                    ->where('status', STATUS_INACTIVE)
                    ->first();

                if (isset($executableOrder) && bccompx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount) == 1) {
                    $availableAmount = bcsubx($executableOrder->amount_in_trade_coin, $executableOrder->executed_amount, 8);

                    if ($requestAmount < $availableAmount) {
                        $closeAmount = $requestAmount;
                        $executedAmount = bcaddx($executableOrder->executed_amount, $requestAmount, 8);
                        $isPosition = FUTURE_TRADE_IS_POSITION;

                        $isBreak = true;
                    } elseif ($requestAmount == $availableAmount) {
                        $closeAmount = $requestAmount;
                        $executedAmount = bcaddx($executableOrder->executed_amount, $requestAmount, 8);
                        $isPosition = FUTURE_TRADE_IS_NOT_POSITION;

                        $isBreak = true;
                    } else {

                        $closeAmount = bcsubx($requestAmount, $availableAmount, 8);
                        $executedAmount = bcaddx($executableOrder->executed_amount, $availableAmount, 8);
                        $isPosition = FUTURE_TRADE_IS_NOT_POSITION;

                        $requestAmount = bcsubx($requestAmount, $availableAmount, 8);
                    }

                    $baseCoinAmount = $closeAmount;
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
            storeException('closeAllLongShortStopMarketLimitOrder', __('Close all order successfully!'));
            return responseData(true, __('Close all order successfully'));
        } catch (\Exception $e) {
            storeException('getFutureTradeSocketData', __('Something went wrong!'));
            return responseData(false, __('Something went wrong'));
        }
    }

    // get tp sl details

    public function getTpSlDetailsData($orderUid, $user)
    {
        $response = responseData(false, __('Something went wrong'));
        try {
            $order = FutureTradeLongShort::where([
                'user_id' => $user->id,
                'uid' => $orderUid
            ])
                ->whereNotNull('parent_id')
                ->first();
            if ($order) {
                $item = FutureTradeLongShort::where([
                    'user_id' => $user->id,
                    'id' => $order->parent_id
                ])
                    ->with('children')
                    ->first();
                if ($item) {
                    $symbol = coinPairSymbol($item->base_coin_id, $item->trade_coin_id);
                    $item->base_coin_type = $symbol['base_coin_type'];
                    $item->trade_coin_type = $symbol['trade_coin_type'];
                    $item->symbol = $symbol['symbol'];
                }
                $response = responseData(true, 'Data get successfully', $item);
            } else {
                $response = responseData(false, 'Data not found');
            }
        } catch (\Exception $e) {
            storeException('getTpSlDetailsData', $e->getMessage());
        }
        return $response;
    }
}
