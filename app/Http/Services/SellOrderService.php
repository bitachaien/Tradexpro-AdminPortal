<?php
namespace App\Http\Services;

use App\Contracts\OrderServiceInterface;
use App\Dtos\OrderCreationDto;
use App\Events\OrderHasPlaced;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\MarketOrderServices\MarketOrderService;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Jobs\PlaceOrderJob;
use App\Model\FavouriteOrderBook;
use App\Model\Sell;
use App\User;
use App\Model\UserWallet;
use App\Services\Order\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellOrderService extends BaseService
{
    public $model = Sell::class;
    public $repository = SellOrderRepository::class;
    public $logger = null;
    public $myCommonService;
    private MarketOrderService $marketOrderService;
    private OrderBookWebsoketService $orderBookWebsoketService;
    private PrivateWsDataService $privateWsDataService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->myCommonService = new MyCommonService();
        $this->logger = app(Logger::class);
        $this->marketOrderService = app()->make(MarketOrderService::class);
        $this->orderBookWebsoketService = app()->make(OrderBookWebsoketService::class);
        $this->privateWsDataService = app()->make(PrivateWsDataService::class);
        $this->orderService = app()->make(OrderService::class);
    }

    public function getOrders()
    {
        return $this->object->getOrders();
    }

    public function getAllOrders($base_coin_id, $trade_coin_id)
    {
        return $this->object->getAllOrders($base_coin_id, $trade_coin_id);
    }

    public function getAllOrderHistory($userId, $search = null, $order_data = null)
    {
        return Sell::where('user_id', $userId)
            ->where('amount', '>', 0)
            ->leftJoin('coins as bc', 'bc.id', '=', 'sells.base_coin_id')
            ->leftJoin('coins as tc', 'tc.id', '=', 'sells.trade_coin_id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('amount', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%")
                        ->orWhere('processed', 'LIKE', "%{$search}%")
                        ->orWhere('bc.coin_type', 'LIKE', "%{$search}%")
                        ->orWhere('tc.coin_type', 'LIKE', "%{$search}%");

                    // Handle status search
                    $searchLower = strtolower($search);
                    if (Str::contains($searchLower, 'pending')) {
                        $q->orWhere('sells.status', STATUS_PENDING);
                    } elseif (Str::contains($searchLower, 'success')) {
                        $q->orWhere('sells.status', STATUS_ACCEPTED);
                    }
                });
            })
            ->select([
                'amount',
                'processed',
                'price',
                'sells.status',
                'sells.created_at',
                'sells.deleted_at',
                'bc.coin_type as base_coin',
                'tc.coin_type as trade_coin',
                DB::raw("'sell' as type")
            ])
            ->when(
                !empty($order_data['column_name']) && !empty($order_data['order_by']),
                function ($query) use ($order_data) {
                    $query->orderBy($order_data['column_name'], $order_data['order_by']);
                },
                function ($query) {
                    $query->orderBy('sells.created_at', 'DESC');
                }
            )
            ->withTrashed();
    }

    public function getTotalAmount($base_coin_id, $trade_coin_id)
    {
        $response = $this->object->getTotalAmount($base_coin_id, $trade_coin_id);
        if (isset($response[0])) {
            $total = $response[0]->total;
        } else {
            $total = '0.00000000';
        }

        return $total;
    }

    public function create(Request $request)
    {
        $coinPairsService = new CoinPairService();
        $coinPairs = $coinPairsService->getDocs(['parent_coin_id' => $request->base_coin_id, 'child_coin_id' => $request->trade_coin_id]);
        if (empty($coinPairs)) {
            return [
                'status' => false,
                'message' => 'Invalid.sell.order.request!',
            ];
        }
        $user = Auth::check() ? Auth::user() : User::find($request->get('user_id'));

        // checking order type
        if (isset($request->is_market) && $request->is_market == 0) {
            $feesZero = isFeesZero($user->id, $request->base_coin_id, $request->trade_coin_id, $request->amount, 'sell', $request->price);
            if ($feesZero) {
                return [
                    'status' => false,
                    'message' => __('Minimum Sell Total Should Be ') . $feesZero
                ];
            }
            $settingTolerance = settings('trading_price_tolerance');
            // checking tolerance if the order category is limit.
            if (bccomp($settingTolerance, '0', 2) > 0) {
                $dashBoardService = new DashboardService();
                $price = $dashBoardService->getTotalVolume($request->base_coin_id, $request->trade_coin_id);
                $lastPrice = isset($price['sell_price']) ? $price['sell_price'] : $coinPairs[0]['price'];

                if ($lastPrice > 0) {
                    $tolerancePrice = bcdiv(bcmul($lastPrice, $settingTolerance), "100");
                    $highTolerance = bcadd($lastPrice, $tolerancePrice);
                    $lowTolerance = bcsub($lastPrice, $tolerancePrice);

                    if (bccomp($request->price, $highTolerance) > 0 || bccomp($request->price, $lowTolerance) < 0) {
                        return [
                            'status' => false,
                            'message' => __("The price must be between :lowTolerance and :highTolerance ", ['lowTolerance' => $lowTolerance, 'highTolerance' => $highTolerance])
                        ];
                    }
                }
            }

            $checkWalletDetails = $this->checkPassiveOrderWallet($request, $user->id);
            if ($checkWalletDetails['status'] == false) {
                return $checkWalletDetails;
            }

            $temporaryFees = calculated_fee_limit($user->id);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => getBtcRate($request->trade_coin_id),
                'user_id' => $user->id
            ]);

            PlaceOrderJob::dispatch(
                $this,
                OrderCreationDto::fromRequest($request),
                false,
                'sell'
            )->onQueue('balance-debit');

            return [
                'status' => true,
                'message' => __('Sell limit order is placed successfully!')
           ];
                
        } else {
            $buyService = new BuyOrderService();
            $buys = $buyService->getDocs(['status' => 0, 'trade_coin_id' => $request->trade_coin_id, 'base_coin_id' => $request->base_coin_id, 'is_market' => 0]);

            if ($buys->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('Buy order not found for this sell order!'),
                ];
            }

            if ($request->get('category', 1) !== 13) {
                $feesZero = isFeesZero($user->id, $request->base_coin_id, $request->trade_coin_id, $request->amount, 'sell');
                if ($feesZero) {
                    return [
                        'status' => false,
                        'message' => __('Minimum Sell Amount Should Be ') . $feesZero
                    ];
                }
            }
            $checkWalletDetails = $this->checkActiveOrderWallet($request, $user->id);
            if ($checkWalletDetails['status'] == false) {
                return $checkWalletDetails;
            }

            $temporaryFees = calculated_fee_limit($user->id);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => getBtcRate($request->trade_coin_id),
                'user_id' => $user->id
            ]);
            
            PlaceOrderJob::dispatch(
                $this,
                OrderCreationDto::fromRequest($request),
                true,
                'sell'
            )->onQueue('balance-debit');

            return [
                'status' => true,
                'message' => __('Sell market order is placed successfully!')
           ];
        }
    }

    // check passive order wallet details
    public function checkPassiveOrderWallet($request, $userId)
    {
        $walletRepository = new UserWalletRepository(UserWallet::class);
        $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);
        if (!$walletDetails) {
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('Invalid sell order request!'),
            ];
        }
        // add and assigning maker and taker fees to the request
        $temporaryFees = calculated_fee_limit($userId);
        $request->merge([
            'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
            'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
            'btc_rate' => getBtcRate($request->trade_coin_id)
        ]);
        // calculate total amount

        $mainBalance = $walletDetails->balance;

        $totalSellCost = custom_number_format($request->amount);
        // checking if available balance is there
        if (bccomp($mainBalance, $totalSellCost) === -1) {
            storeBotException('checkPassiveOrderWallet', 'You need minimum balance  ' . $totalSellCost . ' ' . $walletDetails->coin_type);
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('You need minimum balance  ') . $totalSellCost . ' ' . $walletDetails->coin_type,
            ];
        }
        return [
            'status' => true,
            'message' => __('Success'),
        ];
    }
    // check active order wallet details
    public function checkActiveOrderWallet($request, $userId)
    {
        $walletRepository = new UserWalletRepository(UserWallet::class);
        $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);

        if (!$walletDetails) {
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('Invalid sell order request!'),
            ];
        }

        $temporaryFees = calculated_fee_limit($userId);
        $request->merge([
            'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
            'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
            'btc_rate' => getBtcRate($request->trade_coin_id)
        ]);
        // calculate total amount
        $mainBalance = $walletDetails->balance;

        $totalSellCost = $request->amount;
        $totalSellCost = custom_number_format($totalSellCost);

        // checking if available balance is there
        if ((bccomp($mainBalance, $totalSellCost) === -1) && ($request->get('category', 1) !== 13)) {
            DBService::rollBack();
            storeBotException('checkActiveOrderWallet', 'You need minimum balance  ' . $totalSellCost . ' ' . $walletDetails->coin_type);
            return [
                'status' => false,
                'message' => __('You need minimum balance ') . $totalSellCost . ' ' . $walletDetails->coin_type,
            ];
        }
        return [
            'status' => true,
            'message' => __('Success'),
        ];
    }

    public function placePassiveOrder(OrderCreationDto $request, string $type)
    {
        try {
            $response = false;
            // get sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getWalletAndLock($request->user_id, $request->trade_coin_id);
            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }

            $mainBalance = $walletDetails->balance;

            $totalSellCost = custom_number_format($request->amount);
            // checking if available balance is there
            if (bccomp($mainBalance, $totalSellCost) === -1) {
                DBService::rollBack();
                storeBotException('_passiveSellOrder', 'You need minimum balance  ' . $totalSellCost . ' ' . $walletDetails->coin_type);
                return [
                    'status' => false,
                    'message' => __('You need minimum balance  ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $order = [
                'user_id' => $request->user_id,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => truncate_num($request->amount),
                'processed' => $request->processed,
                'virtual_amount' => $request->virtual_amount,
                'price' => truncate_num($request->price),
                'btc_rate' => $request->btc_rate,
                'is_market' => $request->is_market,
                'maker_fees' => $request->maker_fees,
                'taker_fees' => $request->taker_fees,
                'is_conditioned' => $request->is_conditioned,
                'is_bot' => $request->is_bot ?? 0
            ];
            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }
            if ($sell = $this->object->create($order)) {
            
                DBService::commit();

                $sell['type'] = 'sell';
                $sell['total'] = bcmul($sell->amount, $sell->price, 8);
                $fees = 0;
                if ($sell->maker_fees > $sell->taker_fees) {
                    $fees = bcmul(bcmul(bcmul(bcsub($sell->amount, $sell->processed, 8), $sell->price, 8), $sell->maker_fees, 8), 0.01, 8);
                } else {
                    $fees = bcmul(bcmul(bcmul(bcsub($sell->amount, $sell->processed, 8), $sell->price, 8), $sell->taker_fees, 8), 0.01, 8);
                }
                $sell['fees'] = $fees;
                $this->orderBookWebsoketService->sendData($sell);
                $this->privateWsDataService->sendData($sell);
                event(new OrderHasPlaced($sell));
                return [
                    'status' => true,
                    'message' => __('Sell order is placed successfully!'),
                    'data' => $sell
                ];
            }
        } catch (\Exception $e) {
            DBService::rollBack();
            storeException('_passiveSellOrder ex ', $e->getMessage());
            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function placeActiveOrder(OrderCreationDto $request, string $type)
    {
        try {
            $response = false;
            // get sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getWalletAndLock($request->user_id, $request->trade_coin_id);

            if (!$walletDetails) {
                DBService::rollBack();
                storeBotException('_activeSellOrder $walletDetails', 'Invalid sell order request');
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }

            $mainBalance = $walletDetails->balance;

            $totalSellCost = $request->amount;
            $totalSellCost = custom_number_format($totalSellCost);

            // checking if available balance is there
            if ((bccomp($mainBalance, $totalSellCost) === -1) && ($request->category !== 13)) {
                DBService::rollBack();
                storeBotException('_activeSellOrder', 'You need minimum balance ' . $totalSellCost . ' ' . $walletDetails->coin_type);
                return [
                    'status' => false,
                    'message' => __('You need minimum balance ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $order = [
                'user_id' => $request->user_id,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => truncate_num($request->amount),
                'processed' => $request->processed,
                'virtual_amount' => $request->virtual_amount,
                'price' => $request->price,
                'btc_rate' => $request->btc_rate,
                'is_market' => 1,
                'category' => $request->category,
                'maker_fees' => $request->maker_fees,
                'taker_fees' => $request->taker_fees,
                'is_conditioned' => $request->is_conditioned,
            ];

            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if ($response == false) {
                DBService::rollBack();
                storeException('_activeSellOrder deductBalanceById', 'Failed to place sell order!');
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }
            if ($sell = $this->object->create($order)) {
                storeBotException("ActiveSellOrderPlace", "Sell Id: $sell->id Amount: $sell->amount");

                DBService::commit();
                broadcastWalletData($walletDetails->wallet_id);

                $this->orderBookWebsoketService->sendData($sell);
                $this->privateWsDataService->sendData($sell);

                event(new OrderHasPlaced($sell));

                return [
                    'status' => true,
                    'message' => __('Market sell order is placed successfully!'),
                    'data' => $sell
                ];
            }
        } catch (\Exception $e) {
            DBService::rollBack();
            storeException('_activeSellOrder ex', $e->getMessage());
            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function createMultiSellOrder($request)
    {
        try {
            $userId = Auth::id();
            $response = false;
            // get Sell wallet details
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->trade_coin_id);
            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid sell order request!',
                ];
            }
            $temporaryFees = calculated_fee_limit($userId);
            //            $request->request->add([
//                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
//                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
//                'btc_rate' => 0.005//custom_number_format($btcRate)
//            ]);
            $request->merge([
                'maker_fees' => custom_number_format($temporaryFees['maker_fees']),
                'taker_fees' => custom_number_format($temporaryFees['taker_fees']),
                'btc_rate' => getBtcRate($request->trade_coin_id)
            ]);
            // calculate total amount

            $mainBalance = $walletDetails->balance;
            $inputAmount1 = $request->amount_1;
            $inputAmount2 = $request->amount_2;

            if (isset($request->price_3) && !empty($request->price_3) && isset($request->amount_3) && !empty($request->amount_3)) {
                $inputAmount3 = $request->amount_3;
            } else {
                $inputAmount3 = 0;
            }

            $inputTotal = bcadd($inputAmount1, bcadd($inputAmount2, $inputAmount3));
            $totalSellCost = custom_number_format($inputTotal);

            // checking if available balance is there
            if (bccomp($mainBalance, $totalSellCost) === -1) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You need minimum balance(including fees): ') . $totalSellCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $orders = [];
            $msg1 = $msg2 = $msg3 = "";
            $feesZero1 = $feesZero2 = $feesZero3 = 0;
            $currentTime = Carbon::now();
            if (isset($request->price_1) && !empty($request->price_1) && isset($request->amount_1) && !empty($request->amount_1)) {
                $feesZero1 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_1, 'sell', $request->price_1);
                if ($feesZero1) {
                    $msg1 = __("Sell Total (" . bcmul($request->price_1, $request->amount_1) . ")  Should Not Less Than ") . $feesZero1;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => truncate_num($request->amount_1),
                    'virtual_amount' => $request->get('amount_1') * random_int(20, 80) / 100,
                    'price' => truncate_num($request->price_1),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (isset($request->price_2) && !empty($request->price_2) && isset($request->amount_2) && !empty($request->amount_2)) {
                $feesZero2 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_2, 'sell', $request->price_2);
                if ($feesZero2) {
                    $msg2 = __("Sell Total (" . bcmul($request->price_2, $request->amount_2) . ")  Should Not Less Than ") . $feesZero2;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => truncate_num($request->amount_2),
                    'virtual_amount' => $request->get('amount_2') * random_int(20, 80) / 100,
                    'price' => truncate_num($request->price_2),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (isset($request->price_3) && !empty($request->price_3) && isset($request->amount_3) && !empty($request->amount_3)) {
                $feesZero3 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_3, 'sell', $request->price_3);
                if ($feesZero3) {
                    $msg3 = __("Sell Total (" . bcmul($request->price_3, $request->amount_3) . ")  Should Not Less Than ") . $feesZero3;
                }
                $orders[] = [
                    'user_id' => $userId,
                    'trade_coin_id' => $request->trade_coin_id,
                    'base_coin_id' => $request->base_coin_id,
                    'amount' => truncate_num($request->amount_3),
                    'virtual_amount' => $request->get('amount_3') * random_int(20, 80) / 100,
                    'price' => truncate_num($request->price_3),
                    'btc_rate' => $request->btc_rate,
                    'maker_fees' => $request->maker_fees,
                    'taker_fees' => $request->taker_fees,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ];
            }

            if (empty($orders)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('No order to place'),
                ];
            }
            if ($feesZero1 || $feesZero2 || $feesZero3) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => $msg1 . "</br>" . $msg2 . "</br>" . $msg3,
                ];
            }
            //Deduct Amount from Main Balance
//            $response = getService(['method'=>'deductBalanceById','params'=>['user_id'=>$userId,'coin_id'=>$request->trade_coin_id,'amount'=>$totalSellCost]]);
            $response = $walletRepository->deductBalanceById($walletDetails, $totalSellCost);

            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place sell order!'),
                ];
            }

            if (isset($orders[0])) {
                if ($sell = $this->object->create($orders[0])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    storeBotException("MultiSellOrderPlace", "Sell Details 1: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }
            if (isset($orders[1])) {
                if ($sell = $this->object->create($orders[1])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    storeBotException("MultiSellOrderPlace", "Sell Details 2: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }
            if (isset($orders[2])) {
                if ($sell = $this->object->create($orders[2])) {
                    broadcastOrderData($sell, 'sell', 'orderPlace');
                    storeBotException("MultiSellOrderPlace", "Sell Details 3: Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");
                }
            }

            DBService::commit();
            broadcastWalletData($walletDetails->wallet_id);

            return [
                'status' => true,
                'message' => __('Multi sell order is placed successfully!'),
            ];
        } catch (\Exception $e) {
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place sell order!'),
            ];
        }
    }

    public function getPrice($baseCoinId, $tradeCoinId)
    {
        return $this->object->getPrice($baseCoinId, $tradeCoinId);
    }

    public function getOnOrderBalance($baseCoinId, $tradeCoinId, $userId = null)
    {
        if ($userId == null) {
            $userId = Auth::id();
        }
        return $this->object->getOnOrderBalance($baseCoinId, $tradeCoinId, $userId);
    }

    public function getMyOrders($baseCoinId, $tradeCoinId, $userId)
    {
        return $this->object->getMyOrders($baseCoinId, $tradeCoinId, $userId);
    }

    /**
     * Insert Or Remove OrderBook as favorite
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insertDeleteOrderBookFavorite($request)
    {

        try {
            $obj = FavouriteOrderBook::where([
                'base_coin_id' => $request->base_coin_id,
                'trade_coin_id' => $request->trade_coin_id,
                'price' => $request->price,
                'user_id' => DB::raw(Auth::id()),
                'type' => DB::raw("'sell'")
            ])->first();
            if (is_null($obj)) {
                $isOrder = Sell::where([
                    'base_coin_id' => $request->base_coin_id,
                    'trade_coin_id' => $request->trade_coin_id,
                    'price' => $request->price
                ])->first();
                if (empty($isOrder)) {
                    return response()->json([
                        'status' => false,
                        'message' => __('order.not.found')
                    ]);
                }
                FavouriteOrderBook::create([
                    'base_coin_id' => $request->base_coin_id,
                    'trade_coin_id' => $request->trade_coin_id,
                    'price' => $request->price,
                    'type' => 'sell',
                    'user_id' => DB::raw(Auth::id())
                ]);
                broadcastPrivate('isFavoriteOrderBook', ['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price, 'type' => 'sell', 'action' => 'add'], Auth::id());
                return response()->json([
                    'status' => true,
                    'message' => __('add.to.favorite')
                ]);
            } else {
                $obj->delete();
                broadcastPrivate('isFavoriteOrderBook', ['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price, 'type' => 'sell', 'action' => 'remove'], Auth::id());
                return response()->json([
                    'status' => true,
                    'message' => __('remove.from.favorite')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('failed.to.add.remove.from.favorite')
            ]);
        }

    }

    public function botOrderCreate(Request $request)
    {
        storeBotException('botOrderCreate', date('Y-m-d H:i:s'));
        $request->merge(['is_bot' => 1]);
        $this->orderService->placeOrder(OrderCreationDto::fromRequest($request), 'sell');
    }

    /**
     * Place new bot sell order 12/10/23
     * @param Request $request
     * @return array
     */

    public function createNewBotOrder($orderInfo, $pair, $user)
    {
        try {
            $userId = $user;
            storeBotException('sell pair => ', $pair->pair_bin);
            storeBotException('sell order info => ', $orderInfo);
            storeBotException('createNewBotOrder amount => ', $orderInfo['amount']);
            storeBotException('createNewBotOrder price => ', $orderInfo['price']);
            $amount = cleanAndConvertToDecimal($orderInfo['amount']);
            storeBotException('amount => ', $amount);
            $price = cleanAndConvertToDecimal($orderInfo['price']);
            storeBotException('price => ', $price);
            $order = [
                'user_id' => $userId,
                'trade_coin_id' => $pair->trade_coin_id,
                'base_coin_id' => $pair->base_coin_id,
                'amount' => $amount,
                'virtual_amount' => $amount * random_int(20, 80) / 100,
                'price' => $price,
                'btc_rate' => 0,
                'is_market' => 0,
                'is_bot' => 1
            ];

            // storeBotException('sell order => ', $order);
            if ($sell = $this->object->create($order)) {

                storeBotException("bot sellOrderPlace", "Sell Id: $sell->id Price: $sell->price Amount: $sell->amount");

                $requestData = [
                    'dashboard_type' => 'dashboard',
                    'order_type' => 'sell',
                    'base_coin_id' => $pair->base_coin_id,
                    'trade_coin_id' => $pair->trade_coin_id
                ];
                $request = new Request($requestData);
                // $request->merge([

                // ]);

                $d_service = new DashboardService();

                $socket_data = $d_service->getAllOrderSocketData($request);
                $channel_name = 'dashboard-' . $request->base_coin_id . '-' . $request->trade_coin_id;
                $event_name = 'order_place';
                sendDataThroughWebSocket($channel_name, $event_name, $socket_data);
                // $socket_data2=[];
                // $request->merge(['order_type' => 'buy_sell', 'userId' => $userId,'dashboard_type' => 'dashboard']);
                // $socket_data2['open_orders'] = $d_service->getMyOrders($request)['data'];
                // $socket_data2['order_data'] = $d_service->getOrderDataTotal($request)['data'];
                // $event_name2 = 'order_place_'.$userId;
                // sendDataThroughWebSocket($channel_name,$event_name2,$socket_data2);

                broadcastOrderData($sell, 'sell', 'orderPlace');

                event(new OrderHasPlaced($sell));
                // storeBotException('sendDataThroughWebSocket bot socket data', $socket_data);
                return [
                    'success' => true,
                    'message' => __('Bot Sell order is placed successfully!'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeBotException('createNewBotOrder sell', $e->getMessage());
        }

    }

    public function getAllSellOrders(int $baseCoinId, int $tradeCoinId, int $userId, int $limit = 25)
    {
        return $this->object->getAllSellOrders($baseCoinId, $tradeCoinId, $userId, $limit);
    }
}

