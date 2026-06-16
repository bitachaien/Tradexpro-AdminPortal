<?php

namespace App\Http\Services;

use App\Contracts\Repositories\StopLimitRepositoryInterface;
use App\Events\OrderHasPlaced;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\StopLimitRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\OrderPlacedDataService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Jobs\StopLimitProcessJob;
use App\Model\Buy;
use App\Model\CoinPair;
use App\Model\Sell;
use App\Model\StopLimit;
use App\Services\TradeSettingServices\TradeFeeFinderService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StopLimitService extends CommonService
{
    public $model = StopLimit::class;
    public $repository = StopLimitRepository::class;
    public $logger = null;
    public $myCommonService;

    private OrderBookWebsoketService $orderBookWebsoketService;
    private OrderPlacedDataService $orderPlacedDataService;
    private PrivateWsDataService $privateWsDataService;
    private StopLimitRepositoryInterface $stopLimitRepository;
    private TradeFeeFinderService $tradeFeeFinderService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->myCommonService = new MyCommonService();
        $this->logger = app(Logger::class);
        $this->orderBookWebsoketService = app()->make(OrderBookWebsoketService::class);
        $this->orderPlacedDataService = app()->make(OrderPlacedDataService::class);
        $this->privateWsDataService = app()->make(PrivateWsDataService::class);
        $this->stopLimitRepository = app()->make(StopLimitRepositoryInterface::class);
        $this->tradeFeeFinderService = app()->make(TradeFeeFinderService::class);
    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }


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
        if ($request->order == 'buy') {
            if ($request->stop >= $request->limit) {
                return [
                    'status' => false,
                    'message' => __('Stop value must be less than limit value for buy stop limit')
                ];
            }
        } else {
            if ($request->limit >= $request->stop) {
                return [
                    'status' => false,
                    'message' => __('Stop value must be greater than limit value for sell stop limit')
                ];
            }
        }

        $response = false;
        $user = Auth::check() ? Auth::user() : User::find($request->get('user_id'));

        if (empty($user)) {
            return [
                'status' => false,
                'message' => __('Invalid user')
            ];
        }

        $fees = $this->tradeFeeFinderService->findTradeFee($request->base_coin_id, $request->trade_coin_id, $user->id);

        try {
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(Wallet::class);
            
            if (strtolower($request->order) == 'sell') {
                $walletDetails = $walletRepository->getWalletAndLock($user->id, $request->trade_coin_id);
                $inputTotal = custom_number_format($request->amount);
            } else {
                $walletDetails = $walletRepository->getWalletAndLock($user->id, $request->base_coin_id);
                $feesPercent = $fees->maker_fee > $fees->taker_fee ? $fees->maker_fee : $fees->taker_fee;
                $amountTotal = bcmulx($request->limit, $request->amount);
                $inputTotal = bcaddx($amountTotal, bcdivx(bcmulx($amountTotal, $feesPercent), "100"));
            }

            if (empty($walletDetails)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid wallet',
                ];
            }
            $walletBalance = $walletDetails->balance;

            if (bccompx($walletBalance, $inputTotal) < 0) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You don\'t have enough balance to place a stop limit order.')
                ];
            }

            $stopLimit = [
                'user_id' => $user->id,
                'condition_buy_id' => $request->get('buy_id', null),
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'stop' => custom_number_format($request->stop),
                'limit_price' => custom_number_format($request->limit),
                'amount' => custom_number_format($request->amount),
                'order' => $request->order,
                'is_conditioned' => $request->get('is_conditioned', 0),
                'maker_fees' => $fees->maker_fee,
                'taker_fees' => $fees->taker_fee
            ];

            $response = $walletRepository->deductBalanceById($walletDetails, $inputTotal);
        
            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place stop limit. Please try again!')
                ];
            }

            $inserted = $this->object->create($stopLimit);
            if ($inserted) {
                DBService::commit();
                storeBotException('STOP_LIMIT', 'Stop Limit has been placed id: ' . $inserted->id);
                $this->privateWsDataService->sendData($inserted);

                $repo = new CoinPairRepository(CoinPair::class);
                $coins = $repo->getDocs(['parent_coin_id' => $inserted->base_coin_id, 'child_coin_id' => $inserted->trade_coin_id])->first();

                $this->process($coins);

                return [
                    'status' => true,
                    'message' => __('Stop limit has been placed successfully.'),
                    'data' => $inserted
                ];
            }
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place stop limit. Please try again!')
            ];
        } catch (\Exception $e) {
            DBService::rollBack();
            storeException('STOP_LIMIT_ERROR', 'Error: ' . $e->getMessage() . ' ' . $e->getLine());

            return [
                'status' => false,
                'message' => __('Failed to place stop limit. Please try again!')
            ];
        }
    }

    //    public function getOnOrderBalanceBuy($baseCoinId, $tradeCoinId, $userId = null)
    //    {
    //        if ($userId == null) {
    //            $userId = Auth::id();
    //        }
    //
    //        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId, 'buy');
    //    }
    //
    //    public function getOnOrderBalanceSell($baseCoinId, $tradeCoinId, $userId = null)
    //    {
    //        if ($userId == null) {
    //            $userId = Auth::id();
    //        }
    //
    //        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId, 'sell');
    //    }

    /**
     * Place order of a stop limit
     * @param $coinPair
     * @return bool
     */
    public function process($coinPair)
    {
        $insertedData = [];

        storeBotException('STOP_LIMIT', 'Coin Pair: ' . $coinPair->parent_coin_id . '_' . $coinPair->child_coin_id);

        $stopLimits = $this->object->getDocs(['status' => 0, 'base_coin_id' => $coinPair->parent_coin_id, 'trade_coin_id' => $coinPair->child_coin_id]);
        foreach ($stopLimits as $stopLimit) {

            storeBotException('STOP_LIMIT', 'Start Processing STOP LIMIT ID: ' . $stopLimit->id);
            storeBotException('STOP_LIMIT', 'CoinPrice: ' . $coinPair->price . ' Stop Price: ' . $stopLimit->stop);
            try {
                DBService::beginTransaction();
                $stopLimit = $this->stopLimitRepository->findByIdAndLock($stopLimit->id);
                
                if(! $stopLimit || $stopLimit->status == 1) {
                    DBService::rollBack();
                    continue;
                }

                $input = [
                    'user_id' => $stopLimit->user_id,
                    'base_coin_id' => $stopLimit->base_coin_id,
                    'trade_coin_id' => $stopLimit->trade_coin_id,
                    'amount' => custom_number_format($stopLimit->amount),
                    'virtual_amount' => bcmulx($stopLimit->amount, bcdivx(random_int(20, 80), 100)),
                    'price' => custom_number_format($stopLimit->limit_price),
                    'category' => $stopLimit->category,
                    'is_conditioned' => $stopLimit->is_conditioned,
                    'is_market' => 0,
                    'maker_fees' => $stopLimit->maker_fees,
                    'taker_fees' => $stopLimit->taker_fees
                ];

                if ($stopLimit->condition_buy_id != null) {
                    $input['condition_buy_id'] = $stopLimit->condition_buy_id;
                }

                $inserted = null;

                if (strtolower($stopLimit->order) == 'buy') {
                    //When current price will equal or greater than the stop limit price then a buy order placed.
                    if (bccomp($coinPair->price, $stopLimit->stop) < 0) {
                        DBService::rollBack();
                        continue;
                    }

                    $input['btc_rate'] = getBtcRate($stopLimit->trade_coin_id);
                    $buyOrderRepo = new BuyOrderRepository(Buy::class);
                    $inserted = $buyOrderRepo->create($input);

                    storeBotException('STOP_LIMIT', 'STOP LIMIT Type: Buy');
                } else {
                    //When current price will equal or less than the stop limit price then a sell order placed.
                    if (bccomp($coinPair->price, $stopLimit->stop) > 0) {
                        DBService::rollBack();
                        continue;
                    }

                    $sellOrderRepo = new SellOrderRepository(Sell::class);
                    
                    $input['btc_rate'] = getBtcRate($stopLimit->trade_coin_id);
                    $inserted = $sellOrderRepo->create($input);
                    broadcastOrderData($inserted, 'sell', 'orderPlace', $inserted->user_id);
                    storeBotException('STOP_LIMIT', 'STOP LIMIT Type: Sell');
                }
                if ($inserted) {
                    $stopLimit->update(['status' => 1]);
                    storeBotException('STOP_LIMIT', 'STOP LIMIT ID: ' . $stopLimit->id . ' is closed');
                } else {
                    DBService::rollBack();
                }
                storeBotException('STOP_LIMIT', 'END Processing STOP LIMIT ID: ' . $stopLimit->id);

                DBService::commit();

                if ($inserted) {
                    $this->orderBookWebsoketService->sendData($inserted);
                    $this->privateWsDataService->sendData($inserted);

                    event(new OrderHasPlaced($inserted));
                }

            } catch (\Exception $exception) {
                DBService::rollBack();
                storeException('STOP_LIMIT_ERROR', 'Error: ' . $exception->getMessage());

                return false;
            }
        }

        return true;
    }

    public function getMyStopLimitOrders($request)
    {
        return $this->object->getMyOrders($request);
    }
}
