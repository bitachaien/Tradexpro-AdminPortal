<?php

namespace App\Http\Services;

use App\Model\Buy;
use Carbon\Carbon;
use App\Model\Coin;
use App\Model\Sell;
use App\Model\CoinPair;
use App\Model\StopLimit;
use App\Model\UserWallet;
use App\Model\Transaction;
use App\Jobs\StopLimitProcessJob;
use App\Jobs\TradingViewChartJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Dtos\Calculate24HourPriceDto;
use App\Http\Services\TransactionService;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Repositories\TransactionRepository;
use App\Http\Services\TradeServices\TransactionDataFethcerService;
use App\Contracts\Repositories\TradeTransactionRepositoryInterface;
use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;

class BuySellTransactionService extends BaseService
{
    public $model = Transaction::class;
    public $repository = TransactionRepository::class;

    private $amountToBeProcessed = 0;
    private $buyAbleAmount = 0;
    private $sellAbleAmount = 0;
    private $bot = null;
    public function __construct(
        private TransactionDataFethcerService $transactionDataFethcerService,
        private TradeTransactionRepositoryInterface $tradeTransactionRepository,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory
    )
    {
        parent::__construct($this->model, $this->repository);
    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }

    public function process($orderId, $orderType)
    {
        try {
            $beingProcessType = '';
            if ($orderType == 'buy') {
                $beingProcessType = 'sell';
                $repo = new BuyOrderRepository(Buy::class);
                $order = $repo->getDocs(['id' => $orderId, 'status' => 0])->first();
            } else {
                $beingProcessType = 'buy';
                $repo = new SellOrderRepository(Sell::class);
                $order = $repo->getDocs(['id' => $orderId, 'status' => 0])->first();
            }
            if (empty($order)) {
                $message = __("Order Type: :type Order Id: :orderId Order not found in the database.", ['type' => $orderType, 'orderId' => $orderId]);
                storeBotException('Order', $message);
                return $message;
            }
            //            $baseCoin = $order->baseCoin;
            //            $tradeCoin = $order->tradeCoin;

            //            storeBotException('Order', "Order Type: $orderType Order Amount: $order->amount $tradeCoin->coin_type Order Price: $order->price $baseCoin->coin_type");


            $beingProcessingOrders = $this->_getBeingProcessingOrders($order, $orderType);
            if ($beingProcessingOrders->isEmpty()) {
                //            $this->closeOrder($order, $orderType);
                $message = __("No :orderType order found for this :type order.", ['orderType' => $orderType == 'buy' ? 'sell' : 'buy', 'type' => $orderType]);
                storeBotException('Order', $message);
                return $message;
            }
            foreach ($beingProcessingOrders as $beingProcessingOrder) {
                $price = $beingProcessingOrder->price;

                if ($this->refundIfFeesZero($beingProcessingOrder, $beingProcessType, $price) && $this->refundIfFeesZero($order, $orderType, $price)) {
                    $response = $this->order($order, $beingProcessingOrder, $orderType);
                } else {
                    continue;
                };
                if (!$response) {
                    break;
                }
            }
            $this->closeOrder($order, $orderType);
            $request = [];
            $request['base_coin_id'] = $order->base_coin_id;
            $request['trade_coin_id'] = $order->trade_coin_id;
            $request['dashboard_type'] = 'dashboard';
            $request['per_page'] = 50;
            $request['order_type'] = $beingProcessType;
            //            $time = time();
            //            $interval = 5;
            //            $startTime = $time - 864000;
            //            $endTime = $time;
            $socket_data = [];
            $d_service = new DashboardService();
            $pairService = new CoinPairService();
            $socket_data['pairs'] = $pairService->getAllCoinPairs()['data'];
            $socket_data['trades'] = $d_service->getMarketTransactions((object) $request)['data'];
            $socket_data['last_trade'] = $d_service->getMarketLastTransactions((object) $request)['data'];
            $socket_data['last_price_data'] = $d_service->getDashboardMarketTradeDataTwo($order->base_coin_id, $order->trade_coin_id, 2);
            $socket_data['order_data'] = $d_service->getOrderDataWhenProcess((object) $request)['data'];
            $request['order_type'] = 'buy_sell';
            $socket_data['orders'] = $d_service->getOrders((object) $request)['data'];

            $channel_name = 'trade-info-' . $order->base_coin_id . '-' . $order->trade_coin_id;
            $event_name = 'process';
            //            $socket_data['summary'] = $d_service->getOrderData((object) $request)['data'];
            $socket_data['update_trade_history'] = false;
            sendDataThroughWebSocket($channel_name, $event_name, $socket_data);

            $request['user_id'] = $order->user_id;
            $request['userId'] = $order->user_id;
            $socket_data2['open_orders'] = $d_service->getMyOrders((object) $request)['data'];
            $socket_data2['order_data'] = $d_service->getOrderDataTotal((object) $request)['data'];
            $socket_data2['my_trade'] = $d_service->getMyTradeHistory((object) $request)['data'];
            $event_name3 = 'process-' . $order->user_id;
            sendDataThroughWebSocket($channel_name, $event_name3, $socket_data2);

            // send all buy sell data when transaction happened
            $this->sendBuySellDataWhenTransactionDone($order);

            return true;
        } catch (\Exception $e) {
            storeException('process ex order id', $orderId);
            storeException('process ex order type', $orderType);
            storeException('process ex', $e->getMessage());
            return false;
        }
    }

    // send websocket buy sell data when transaction happened
    public function sendBuySellDataWhenTransactionDone($order)
    {
        $request = [];
        $request['base_coin_id'] = $order->base_coin_id;
        $request['trade_coin_id'] = $order->trade_coin_id;
        $request['price'] = $order->price;
        $request['amount'] = $order->amount;
        $request['dashboard_type'] = 'dashboard';
        $request['per_page'] = 50;
        $request['order_type'] = 'buy_sell';

        $d_service = new DashboardService();
        $socket_data = $d_service->getAllOrderSocketData((object) $request);
        $channel_name = 'dashboard-' . $order->base_coin_id . '-' . $order->trade_coin_id;
        $event_name = 'order_place';
        sendDataThroughWebSocket($channel_name, $event_name, $socket_data);
    }

    /**
     * if fees 0 then refund order
     * @param $order
     * @param $type
     * @return bool
     */
    public function refundIfFeesZero($order, $type, $price)
    {
        DB::beginTransaction();
        try {
            if ($type == 'buy') {
                $order = Buy::lockForUpdate()->find($order->id);

                $amount = custom_number_format(bcsubx($order->amount, $order->processed));
                if ($order->taker_fees != 0) {
                    $buyFees = bcdivx(bcmulx($price, bcmulx($amount, $order->taker_fees)), 100);
                } else {
                    $buyFees = 1;
                }
                if ($order->maker_fees != 0) {
                    $sellFees = bcdivx(bcmulx($price, bcmulx($amount, $order->maker_fees)), 100);
                } else {
                    $sellFees = 1;
                }

                $adjustValue = bcaddx(bcmulx($price, $amount), 0);
                $coinId = $order->base_coin_id;
            } else {
                $order = Sell::lockForUpdate()->find($order->id);

                $amount = custom_number_format(bcsubx($order->amount, $order->processed));
                if ($order->taker_fees != 0) {
                    $buyFees = bcdivx(bcmulx($price, bcmulx($amount, $order->taker_fees)), 100);
                } else {
                    $buyFees = 1;
                }
                if ($order->maker_fees != 0) {
                    $sellFees = bcdivx(bcmulx($price, bcmulx($amount, $order->maker_fees)), 100);
                } else {
                    $sellFees = 1;
                }

                $adjustValue = $amount;
                $coinId = $order->trade_coin_id;
            }

            // check if fees 0, if true then refund
            if (bccompx($buyFees, "0") === 0 || bccompx($sellFees, "0") === 0) {

                storeBotException('OrderProcessing', "Return for fees 0");
                $walletRepo = new UserWalletRepository(UserWallet::class);
                $wallet = $walletRepo->getDocs(['user_id' => $order->user_id, 'coin_id' => $coinId])->first();
                $wallet->increment('balance', $adjustValue);
                if ($type == 'buy') {
                    $model = new Buy();
                } else {
                    $model = new Sell();
                }

                if (bccompx(truncate_num($order->processed), "0") === 0) {
                    $model->find($order->id)->delete();
                } else {
                    $model->where(['id' => $order->id, 'status' => 0])->update(['amount' => $order->processed, 'status' => 1]);
                }
                $order->amount = $order->processed;
                DB::commit();

                return false;
            } else {
                DB::rollBack();
                return true;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function refundIfFeesZeroMarket($order, $price)
    {
        $order = Buy::find($order->id);
        $total = custom_number_format(bcsubx($order->request_amount, $order->processed_request_amount));
        if ($order->taker_fees != 0) {
            $buyFees = bcdivx(bcmulx($total, $order->taker_fees), 100);
        } else {
            $buyFees = 1;
        }
        if ($order->maker_fees != 0) {
            $sellFees = bcdivx(bcmulx($total, $order->maker_fees), 100);
        } else {
            $sellFees = 1;
        }


        $adjustValue = $total;
        $coinId = $order->base_coin_id;

        // check if fees 0, if true then refund
        if (bccompx($adjustValue, 0) !== 0 && (bccompx($buyFees, "0") === 0 || bccompx($sellFees, "0") === 0)) {
            DB::beginTransaction();
            try {
                storeBotException('OrderProcessing', "Return $adjustValue for fees 0");
                $walletRepo = new UserWalletRepository(UserWallet::class);
                $wallet = $walletRepo->getDocs(['user_id' => $order->user_id, 'coin_id' => $coinId])->first();
                $wallet->increment('balance', $adjustValue);
                $model = new Buy();


                if (bccompx(truncate_num($order->processed_request_amount), "0") === 0) {
                    $model->find($order->id)->delete();
                } else {
                    $model->where(['id' => $order->id, 'status' => 0])->update(['request_amount' => $order->processed_request_amount, 'status' => 1]);
                }
                DB::commit();


                //                broadcastWalletData($wallet->id, $order->user_id);
                return false;
            } catch (\Exception $e) {
                DB::rollBack();
                return false;
            }
        } else {
            return true;
        }
    }

    public function _getBeingProcessingOrders($order, $type)
    {

        $condition = [
            'base_coin_id' => $order->base_coin_id,
            'trade_coin_id' => $order->trade_coin_id,
            'status' => 0,
            'is_market' => 0,
        ];
        if ($type == 'buy') {
            $repo = new SellOrderRepository(Sell::class);
            if ($order->is_market == 0) {
                $condition = array_merge($condition, ['price' => ['<=', $order->price]]);
            }
            return $repo->getDocs($condition, null, ['price' => 'asc']);
        } else {
            $repo = new BuyOrderRepository(Buy::class);
            if ($order->is_market == 0) {
                $condition = array_merge($condition, ['price' => ['>=', $order->price]]);
            }
            return $repo->getDocs($condition, null, ['price' => 'desc']);
        }
    }

    public function order($order, $beingProcessingOrder, $orderType)
    {
        $superAdminId = get_super_admin_id();
        storeBotException('order function ', "called");
        DB::beginTransaction();
        try {
            if ($orderType == 'buy') {
                $buy = Buy::lockForUpdate()->find($order->id);
                $sell = Sell::lockForUpdate()->find($beingProcessingOrder->id);
            } else {
                $buy = Buy::lockForUpdate()->find(id: $beingProcessingOrder->id);
                $sell = Sell::lockForUpdate()->find($order->id);
            }

            if(!$buy || !$sell) {
                DB::rollBack();
                return false;
            }

            $this->sellAbleAmount = bcsub(truncate_num($sell->amount), truncate_num($sell->processed));
            $this->buyAbleAmount = bcsub(truncate_num($buy->amount), truncate_num($buy->processed));


            if (bccompx($this->buyAbleAmount, "0") === 0 || bccompx($this->sellAbleAmount, "0") === 0) {
                // Extra check if any Available Amount 0
                storeBotException('OrderProcessing', "Order processing failed(1).");
                DB::rollBack();
                return true;
            }

            storeBotException('OrderProcessing', "Order processing start ..................");
            storeBotException('OrderProcessing', "Buy ID: $buy->id OrderType: $buy->is_market Price: $buy->price Amount: $buy->amount Processed: $buy->processed Remaining: $this->buyAbleAmount");
            storeBotException('OrderProcessing', "Sell ID: $sell->id OrderType: $sell->ordertype Price: $sell->price Amount: $sell->amount Processed: $sell->processed Remaining: $this->sellAbleAmount");

            if (bccompx($this->buyAbleAmount, $this->sellAbleAmount) !== 1) {
                $this->amountToBeProcessed = $this->buyAbleAmount;
            } else {
                $this->amountToBeProcessed = $this->sellAbleAmount;
            }

            storeBotException('OrderProcessing', "Processable Amount: $this->amountToBeProcessed");

            $input = $this->transactionDataFethcerService
                ->fetchTransactionData($buy, $sell, $this->amountToBeProcessed);

            if (bccompx($this->buyAbleAmount, $this->sellAbleAmount) == -1) {
                $buy->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
                $sell->increment('processed', $this->amountToBeProcessed);
            } else if (bccompx($this->buyAbleAmount, $this->sellAbleAmount) == 0) {
                $sell->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
                $buy->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
            } else if (bccompx($this->buyAbleAmount, $this->sellAbleAmount) == 1) {
                $buy->increment('processed', $this->amountToBeProcessed);
                $sell->increment('processed', $this->amountToBeProcessed, ['status' => 1]);
            }
            storeBotException('OrderProcessing', "Buy and Sell updated.");
            storeBotException('Transaction data', json_encode($input));

            $transaction = $this->tradeTransactionRepository->create($input);

            if (!$buy->is_conditioned) {
                if (!$this->_updateTradeCoinWallet($transaction, $buy, $sell, $this->amountToBeProcessed, $input->buy_fees, $input->sell_fees, $superAdminId)) {
                    DB::rollBack();
                    return true;
                }
                storeBotException('OrderProcessing', "Wallet's Trade Coin balance updated.");
            }

            if (!$this->_updateBaseCoinWallet($sell, $transaction, $input->sell_fees)) {
                DB::rollBack();
                if ($orderType == 'buy') {
                    $ord = Buy::find($order->id);
                } else {
                    $ord = Sell::find($order->id);
                }
                $this->closeOrder($ord, $orderType);
                return true;
            }
            storeBotException('OrderProcessing', "Wallet's base Coin balance updated.");

            $transactionId = isset($transactionId) ? $transactionId : time() . fixedlenstr($transaction->id);
            $this->object->updateWhere(['id' => $transaction->id], ['transaction_id' => $transactionId]);
            storeBotException('OrderProcessing', "Transaction updated.");

            $this->update24HourPrice($transaction);

            $this->_checkConditionedOrders($transaction);

            storeBotException('transaction -->', json_encode($transaction));

            DB::commit();
            dispatch(new TradingViewChartJob($transaction))->onQueue('update-candlesticks');
            storeBotException('OrderProcessing', "Order processing end ..................");


            //dispatch(new BroadcastOrderBookBookmarkJob($transaction))->onQueue('broadcast-orderbook-bookmark');

            if (($orderType == 'buy' && $buy->status == 1) || ($orderType == 'sell' && $sell->status == 1)) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            storeBotException('OrderProcessing', $e);
            DB::rollBack();
            // storeBotException('OrderProcessing', "Error:" . $e->getMessage() . ' Line: ' . $e->getLine() . "\n\n");
            return true;
        }
    }
    public function update24HourPrice($transaction)
    {
        try {
            $pair = CoinPair::where(['parent_coin_id' => $transaction->base_coin_id, 'child_coin_id' => $transaction->trade_coin_id])->first();
            $tData = Transaction::select(
                DB::raw('max(price) as max'), 
                    DB::raw('min(price) as min'), 
                    DB::raw('sum(amount) as total'),
                    DB::raw('sum(price * amount) as base_total')
                )
                ->where(['base_coin_id' => $transaction->base_coin_id, 'trade_coin_id' => $transaction->trade_coin_id])
                ->where('created_at', '>=', Carbon::now()->subDays(1))
                ->groupBy(['base_coin_id', 'trade_coin_id'])
                ->first();

            if ($pair->price != $transaction->price)
                    StopLimitProcessJob::dispatch($pair->id)->onQueue('stop-limit');

            $price24hChange = (new TransactionService)->calculate24HourPrice(Calculate24HourPriceDto::fromCoinPair($pair), $transaction);

            $pair->update([
                'change' => $price24hChange ?? $pair->change,
                'price' => $transaction->price,
                'high' => $tData->max,
                'low' => $tData->min,
                'volume' => $tData->total
            ]);

            return true;
        } catch (\Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            return false;
        }
    }

    /**
     * update trade coin wallet after transaction
     * @param $transaction
     * @param $buy
     * @param $sell
     * @param $amount
     * @param $buyFees
     * @param $sellFees
     * @return bool
     */
    public function _updateTradeCoinWallet($transaction, $buy, $sell, $amount, $buyFees, $sellFees, $superAdminId)
    {
        try {
            storeBotException('OrderProcessing', "Start update user coin balance ..................");
            $walletRepo = new UserWalletRepository(UserWallet::class);

            $tradeCoin = Coin::find($buy->trade_coin_id);
            if ($buy->user_id != 1) {
                $buyerTradeCoinWallet = $walletRepo->getDocs(['user_id' => $buy->user_id, 'coin_type' => $tradeCoin->coin_type])->first();
                storeBotException('OrderProcessing', "trade coin wallet data..................");
                storeBotException('OrderProcessing', json_encode($buyerTradeCoinWallet));
                storeBotException('OrderProcessing', "Buy User Coin Balance Before Update: " . $buyerTradeCoinWallet->balance);
                $updateBuyerTradeCoinWallet = $buyerTradeCoinWallet->increment('balance', $amount);
                if (empty($updateBuyerTradeCoinWallet)) {
                    storeBotException('OrderProcessing', "Buyer coin wallet update failed.");
                    return false;
                }
                storeBotException('OrderProcessing', "Buy User Coin Balance After Update:" . $buyerTradeCoinWallet->balance);

                //refund if buy price is big than sell
                $baseCoin = Coin::find($buy->base_coin_id);
                $buyerBaseCoinWallet = $walletRepo->getDocs(['user_id' => $buy->user_id, 'coin_type' => $baseCoin->coin_type])->first();
                storeBotException('OrderProcessing', "Buy User Base Coin Balance Before Adjust: " . $buyerBaseCoinWallet->balance);

                $buyTotalOld = bcaddx(bcmulx($buy->price, $amount), bcmulx(bcmulx(bcmulx($buy->price, $amount), $buy->taker_fees), "0.01"));
                //$buyTotalNew = bcaddx(bcmulx($transaction->price, $amount), $buyFees);
                $buyTotalNew = bcaddx(bcmulx($transaction->price, $amount), bcmulx(bcmulx(bcmulx($transaction->price, $amount), $buyFees), "0.01"));
                storeBotException('OrderProcessing', "buyTotalOld  :" . $buyTotalOld);
                storeBotException('OrderProcessing', "buyTotalNew  :" . $buyTotalNew);
                storeBotException('OrderProcessing', "buyFees  :" . $buyFees);
                storeBotException('OrderProcessing', "sellFees  :" . $sellFees);

                $adjustValue = bcsubx($buyTotalOld, $buyTotalNew);
                storeBotException('OrderProcessing', "Adjustment Value :" . $adjustValue . " that will refunded or deduct");
                $updateBuyerBaseCoinWallet = $walletRepo->getDocs(['user_id' => $buy->user_id, 'coin_type' => $baseCoin->coin_type])->first();
                storeBotException('Update Object', ">>>>>>>" . json_encode($updateBuyerBaseCoinWallet));
                
                if(
                    $buy->user_id != $superAdminId && 
                    bccomp(bcadd($adjustValue, $updateBuyerBaseCoinWallet->balance), 0 ) == -1   
                ) {
                    return false;
                }

                if (bccomp($adjustValue, 0) !== 0) {
                    $isUpdateBalance = $updateBuyerBaseCoinWallet->increment('balance', $adjustValue);
                    if (empty($isUpdateBalance)) {
                        storeBotException('OrderProcessing', "Buyer Base coin wallet Adjust failed. >>>" . $isUpdateBalance);
                        storeBotException('OrderProcessing>>>>', "user_id => " . $buy->user_id . "coin_id => " . $buy->base_coin_id);
                        return false;
                    }
                }
                storeBotException('OrderProcessing', "Buy User Base Coin Balance After Adjust: " . $buyerBaseCoinWallet->balance);
            }
        } catch (\Exception $e) {
            storeException('OrderProcessing', "Update coin wallet failed. Message: " . $e->getMessage() . " Line: " . $e->getLine() . " File: " . $e->getFile());
            return false;
        }
        return true;
    }

    /**
     * update base coin wallet after transaction
     * @param $sell
     * @param $transaction
     * @param $sell_fees
     * @return bool
     */
    public function _updateBaseCoinWallet($sell, $transaction, $sell_fees)
    {
        try {
            storeBotException('OrderProcessing', "Start update user base coin balance ..................");
            $walletRepo = new UserWalletRepository(UserWallet::class);

            $baseCoin = Coin::find($sell->base_coin_id);
            if ($sell->user_id != 1) {
                $sellerDeposit = $walletRepo->getDocs(['user_id' => $sell->user_id, 'coin_type' => $baseCoin->coin_type])->first();

                storeBotException('OrderProcessing', "Sell User BaseCoin Balance Before Update: " . $sellerDeposit->balance);
                $updateSellBalance = bcsubx($transaction->total, bcmulx(bcmulx($transaction->total, $sell_fees), "0.01"));
                if ($updateSellBalance == 0) {
                    return false;
                }
                $updateSellerBaseCoinWallet = $sellerDeposit->increment('balance', $updateSellBalance);
                if (empty($updateSellerBaseCoinWallet)) {
                    storeBotException('OrderProcessing', "Seller base coin wallet update failed.");
                    return false;
                }
                storeBotException('OrderProcessing', "Sell User BaseCoin Balance After Update: " . $sellerDeposit->balance);
            }
        } catch (\Exception $e) {
            storeBotException('OrderProcessing', "Update base coin wallet failed. Message: " . $e->getMessage() . " Line: " . $e->getLine() . " File: " . $e->getFile());
            return false;
        }
        return true;
    }

    public function closeOrder($order, $orderType)
    {
        DB::beginTransaction();
        try {
            if ($order->is_market == 1 && $order->status == 0 && bccompx($order->amount, truncate_num($order->processed))) {
                $amount = bcsubx($order->amount, truncate_num($order->processed));
                if ($orderType == 'buy') {
                    $userFees = bccompx($order->maker_fees, $order->taker_fees) >= 0 ?
                        $order->maker_fees : $order->taker_fees;
                    $adjustValue = bcaddx(bcmulx($order->price, $amount), bcmulx(bcmulx(bcmulx($order->price, $amount), $userFees), "0.01"));
                    $coinId = $order->base_coin_id;
                } else {
                    $adjustValue = $amount;
                    $coinId = $order->trade_coin_id;
                }
                $walletRepo = new UserWalletRepository(UserWallet::class);
                $wallet = $walletRepo->getDocs(['user_id' => $order->user_id, 'coin_id' => $coinId])->first();
                $wallet->increment('balance', $adjustValue);
                if (bccompx(truncate_num($order->processed), "0") == 0) {
                    $order->delete();
                } else {
                    $order->amount = $order->processed;
                    $order->status = 1;
                    $order->update();
                }
            } else if ($order->is_market == 1 && $order->status == 0 && $order->amount == $order->processed) {
                $order->status = 1;
                $order->update();
                storeBotException('OrderServiceException', "Update order status 0 to 1.");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('OrderServiceException', "Update order failed. Message: " . $e->getMessage() . " Line: " . $e->getLine() . " File: " . $e->getFile());
        }
    }

    private function _checkConditionedOrders($transaction)
    {
        storeBotException('CheckConditionBuyOrder', 'Start....');
        $buyOrderRepo = new BuyOrderRepository(Buy::class);
        $buyOrder = $buyOrderRepo->getById($transaction->buy_id);

        storeBotException('CheckBuyOrder', 'Start....');

        if (!empty($buyOrder) && $buyOrder->status == 1 && $buyOrder->is_conditioned) {
            storeBotException('CheckBuyOrder', 'Dispatch buy job....');
        }

        storeBotException('CheckBuyOrder', 'End....');
        $sellOrderRepo = new SellOrderRepository(Sell::class);
        $sellOrder = $sellOrderRepo->getById($transaction->sell_id);

        storeBotException('CheckSellOrder', 'Start....');
        if (!empty($sellOrder) && $sellOrder->status == 1 && $sellOrder->is_conditioned) {
            $unprocessedSellOrders = $sellOrderRepo->getDocs(['condition_buy_id' => $sellOrder->condition_buy_id, 'status' => 0]);
            if (count($unprocessedSellOrders) == 0) {
                $stopLimitRepo = new StopLimitRepository(StopLimit::class);
                $stopLimit = $stopLimitRepo->getDocs(['condition_buy_id' => $sellOrder->condition_buy_id, 'status' => 0]);

                storeException('CheckStopLimit', json_encode($stopLimit));
                $stopLimit = $stopLimit->first();
                if (!empty($stopLimit)) {
                    storeBotException('CheckSellOrder', 'Delete Stop Limit.....');
                    $stopLimit->delete();
                }
            }
        }
        storeBotException('CheckSellOrder', 'End....');
    }

    public function getMyTradeHistory($baseCoinId, $tradeCoinId, $userId, $orderType = null, $duration = null)
    {
        $select = ['transaction_id', DB::raw("CASE WHEN buy_user_id =" . getUserId() . " THEN buy_fees WHEN sell_user_id =" . getUserId() . " THEN sell_fees END as fees"), DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"), 'price_order_type', DB::raw("visualNumberFormat(total) as total"), 'created_at', DB::raw("TIME(created_at) as time")];
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];
        $time = 0;
        $orWhere = [];
        if (Auth::guard('api')->check()) {

            if (empty($orderType)) {
                $where['buy_user_id'] = $userId;
                $orWhere = [
                    'sell_user_id' => $userId,
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                ];
            } else {
                if ($orderType == 'buy') {
                    $where['buy_user_id'] = $userId;
                    $select[] = 'buy_fees as fees';
                } else {
                    $where['sell_user_id'] = $userId;
                    $select[] = 'sell_fees as fees';
                }
            }
        } else {
            if (empty($orderType)) {
                $where['buy_user_id'] = 0;
                $orWhere = [
                    'sell_user_id' => 0,
                    'base_coin_id' => $baseCoinId,
                    'trade_coin_id' => $tradeCoinId,
                ];
            } else {
                if ($orderType == 'buy') {
                    $where['buy_user_id'] = 0;
                    $select[] = 'buy_fees as fees';
                } else {
                    $where['sell_user_id'] = 0;
                    $select[] = 'sell_fees as fees';
                }
            }
        }

        if (!empty($duration) || ($duration != 0)) {
            $time = Carbon::now()->subDays($duration);
        }

        return $this->object->getMyTradeHistory($select, $where, $orWhere, $time);
    }

    public function getMyAllTransactionHistory($userId)
    {
        $select = [
            'transaction_id',
            DB::raw("CASE WHEN buy_user_id =" . Auth::id() . " THEN buy_fees WHEN sell_user_id =" . Auth::id() . " THEN sell_fees END as fees"),
            DB::raw("visualNumberFormat(amount) as amount"),
            DB::raw("bc.coin_type as base_coin"),
            DB::raw("tc.coin_type as trade_coin"),
            DB::raw("visualNumberFormat(price) as price"),
            DB::raw("visualNumberFormat(last_price) as last_price"),
            'price_order_type',
            DB::raw("visualNumberFormat(total) as total"),
            DB::raw("transactions.created_at as time")
        ];
        $where = [];
        $time = 0;
        $where['buy_user_id'] = $userId;
        $orWhere = [
            'sell_user_id' => $userId,
        ];

        return $this->object->getMyAllTradeHistory($select, $where, $orWhere);
    }



    public function getAllTradeHistory($baseCoinId, $tradeCoinId)
    {
        $where = [
            'base_coin_id' => $baseCoinId,
            'trade_coin_id' => $tradeCoinId,
        ];

        return $this->object->getAllTradeHistory($where);
    }
}
