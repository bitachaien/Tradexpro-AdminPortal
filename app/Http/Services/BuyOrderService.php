<?php

namespace App\Http\Services;

use App\Contracts\OrderServiceInterface;
use App\Dtos\OrderCreationDto;
use App\Events\OrderHasPlaced;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\MarketOrderServices\MarketOrderService;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Jobs\PlaceOrderJob;
use App\Model\Buy;
use App\Model\FavouriteOrderBook;
use App\User;
use App\Model\UserWallet;
use App\Services\Order\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuyOrderService extends BaseService
{
    public $model = Buy::class;
    public $repository = BuyOrderRepository::class;
    public $myCommonService;
    private MarketOrderService $marketOrderService;
    private OrderBookWebsoketService $orderBookWebsoketService;
    private PrivateWsDataService $privateWsDataService;
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct($this->model, $this->repository);
        $this->myCommonService = new MyCommonService;
        $this->marketOrderService = app()->make(MarketOrderService::class);
        $this->orderBookWebsoketService = app()->make(OrderBookWebsoketService::class);
        $this->privateWsDataService = app()->make(PrivateWsDataService::class);
        $this->orderService = app()->make(OrderService::class);
    }

    /**
     * Place buy order
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        $coinPairsService = new CoinPairService();
        $coinPairs = $coinPairsService->getDocs(['parent_coin_id' => $request->base_coin_id, 'child_coin_id' => $request->trade_coin_id]);
        if (empty($coinPairs)) {
            return [
                'status' => false,
                'message' => __('Invalid buy order request '),
            ];
        }

        $user = Auth::check() ? Auth::user() : User::find($request->get('user_id'));
        // checking order type

        if (isset($request->is_market) && $request->is_market == 0) {
            $feesZero = isFeesZero($user->id, $request->base_coin_id, $request->trade_coin_id, $request->amount, 'buy', $request->price);
            if ($feesZero) {
                return [
                    'status' => false,
                    'message' => __('Minimum Buy Total Should Be ') . $feesZero
                ];
            }
            $settingTolerance = settings('trading_price_tolerance');
            // checking tolerance if the order category is limit.
            if (bccompx($settingTolerance, '0', 2) > 0) {
                $dashBoardService = new DashboardService();
                $price = $dashBoardService->getTotalVolume($request->base_coin_id, $request->trade_coin_id);

                $lastPrice = isset($price['buy_price']) ? $price['buy_price'] : $coinPairs[0]['price'];

                if ($lastPrice > 0) {
                    $tolerancePrice = bcdivx(bcmulx($lastPrice, $settingTolerance), "100");
                    $highTolerance = bcaddx($lastPrice, $tolerancePrice);
                    $lowTolerance = bcsubx($lastPrice, $tolerancePrice);

                    if (bccompx($request->price, $highTolerance) > 0 || bccompx($request->price, $lowTolerance) < 0) {
                        storeBotException('buy create tolerance check', __("The price must be between :lowTolerance and :highTolerance ", ['lowTolerance' => $lowTolerance, 'highTolerance' => $highTolerance]));
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
                'buy'
            )->onQueue('balance-debit');

            return [
                'status' => true,
                'message' => __('Buy limit order is placed successfully!')
            ];

        } else {
            $sellService = new SellOrderService();
            $sells = $sellService->getDocs(['status' => 0, 'trade_coin_id' => $request->trade_coin_id, 'base_coin_id' => $request->base_coin_id, 'is_market' => 0]);

            if ($sells->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('Sell order not found for this buy order!'),
                ];
            }

            $feesZero = isFeesZeroForMarket($user->id, $request->amount);
            if ($feesZero) {
                return [
                    'status' => false,
                    'message' => __('Minimum Buy Amount Should Be ') . $feesZero
                ];
            }

            $request->merge([
                'price' => $this->marketOrderService->getCurrentMarketPrice(
                    $request->get('base_coin_id'),
                    $request->get('trade_coin_id')
                )
            ]);

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
                'buy'
            )->onQueue('balance-debit');

            return [
                'status' => true,
                'message' => __('Buy limit order is placed successfully!')
            ];
        }
    }

    // check passive order wallet details
    public function checkPassiveOrderWallet($request, $userId)
    {
        $walletRepository = new UserWalletRepository(UserWallet::class);
        $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->base_coin_id);

        if (!$walletDetails) {
            return [
                'status' => false,
                'message' => __('Invalid buy order request'),
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

        $totalBuyCost = $this->_getTotalBuyCost(
            $request->price,
            $request->amount,
            $request->maker_fees,
            $request->taker_fees
        );

        $totalBuyCost = custom_number_format($totalBuyCost);
        // checking if available balance is there
        if (bccompx($mainBalance, $totalBuyCost) === -1) {
            DBService::rollBack();
            storeBotException('_passiveBuyOrder', 'You need minimum balance(including fees):' . $totalBuyCost . ' ' . $walletDetails->coin_type);
            return [
                'status' => false,
                'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type,
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
        $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->base_coin_id);

        if (!$walletDetails) {
            DBService::rollBack();
            return [
                'status' => false,
                'message' => 'Invalid buy order request!',
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

        $totalBuyCost = $this->_getTotalBuyCost(
            $request->price,
            $request->amount,
            $request->maker_fees,
            $request->taker_fees
        );

        $totalBuyCost = custom_number_format($totalBuyCost);

        // checking if available balance is there
        if ((bccompx($mainBalance, $totalBuyCost) === -1) && ($request->get('category', 1) !== 13)) {
            DBService::rollBack();
            storeBotException('checkActiveOrderWallet', 'You need minimum balance(including fees): ' . $totalBuyCost . ' ' . $walletDetails->coin_type);
            return [
                'status' => false,
                'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type,
            ];
        }
        return [
            'status' => true,
            'message' => __('Success'),
        ];
    }

    /**
     * Place market buy order
     * @param $request
     * @param $userId
     * @return array
     */
    public function placeActiveOrder(OrderCreationDto $request, string $type)
    {
        try {
            $response = false;
            DBService::beginTransaction();

            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getWalletAndLock($request->user_id, $request->base_coin_id);

            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid buy order request!',
                ];
            }

            // calculate total amount
            $mainBalance = $walletDetails->balance;

            $totalAmount = bcmulx($request->price, $request->amount);
            $fees = $request->maker_fees > $request->taker_fees ? $request->maker_fees : $request->taker_fees;
            $totalFees = bcdivx(bcmulx($totalAmount, $fees), "100");
            $totalBuyCost = bcaddx($totalAmount, $totalFees);
            $totalBuyCost = custom_number_format($totalBuyCost);

            // checking if available balance is there
            if ((bccompx($mainBalance, $totalBuyCost) === -1) && ($request->get('category', 1) !== 13)) {
                DBService::rollBack();
                storeBotException('_activeBuyOrder', 'You need minimum balance(including fees): ' . $totalBuyCost . ' ' . $walletDetails->coin_type);
                return [
                    'status' => false,
                    'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type,
                ];
            }

            $order = [
                'user_id' => $request->user_id,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => truncate_num($request->amount),
                'request_amount' => 0,
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

            $response = $walletRepository->deductBalanceById($walletDetails, $totalBuyCost);

            if ($response == false) {
                DBService::rollBack();
                storeBotException('_activeBuyOrder deductBalanceById', 'Failed to place buy order');
                return [
                    'status' => false,
                    'message' => __('Failed to place buy order !'),
                ];
            }
            if ($buy = $this->object->create($order)) {
                storeBotException('ActiveBuyOrderPlace ', "Buy Id: $buy->id Request : $buy->amount Want to spend");
                DBService::commit();

                $this->orderBookWebsoketService->sendData($buy);
                $this->privateWsDataService->sendData($buy);

                event(new OrderHasPlaced($buy));

                return [
                    'status' => true,
                    'message' => __('Market buy order is placed successfully!'),
                    'data' => $buy
                ];
            }
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place buy order !'),
            ];
        } catch (\Exception $e) {
            storeException('_activeBuyOrder ex ', $e->getMessage());
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('Failed to place buy order!'),
            ];
        }
    }

    /**
     * Place normal buy order
     * @param $request
     * @param $userId
     * @return array
     */
    public function placePassiveOrder(OrderCreationDto $request, string $type)
    {
        try {
            $response = false;
            // get buy wallet details
            DBService::beginTransaction();
            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getWalletAndLock($request->user_id, $request->base_coin_id);

            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid buy order request!',
                ];
            }

            $mainBalance = $walletDetails->balance;

            $totalBuyCost = $this->_getTotalBuyCost(
                $request->price,
                $request->amount,
                $request->maker_fees,
                $request->taker_fees
            );

            $totalBuyCost = custom_number_format($totalBuyCost);
            // checking if available balance is there
            if (bccompx($mainBalance, $totalBuyCost) === -1) {
                DBService::rollBack();
                storeBotException('_passiveBuyOrder', 'You need minimum balance(including fees):' . $totalBuyCost . ' ' . $walletDetails->coin_type);
                return [
                    'status' => false,
                    'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type,
                ];
            }
            $order = [
                'user_id' => $request->user_id,
                'trade_coin_id' => $request->trade_coin_id,
                'base_coin_id' => $request->base_coin_id,
                'amount' => custom_number_format($request->amount),
                'virtual_amount' => $request->virtual_amount,
                'price' => custom_number_format($request->price),
                'btc_rate' => 0,
                'is_market' => 0,
                'maker_fees' => $request->maker_fees,
                'taker_fees' => $request->taker_fees,
                'is_conditioned' => $request->is_conditioned,
                'is_bot' => $request->is_bot ?? 0
            ];
            $response = $walletRepository->deductBalanceById($walletDetails, $totalBuyCost);
            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place buy order!'),
                ];
            }
            if ($buy = $this->object->create($order)) {

                storeBotException("NormalBuyOrderPlace", "Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");

                DBService::commit();

                //broadcastOrderData($buy, 'buy', 'orderPlace');

                //broadcastWalletData($walletDetails->wallet_id);
                $buy['type'] = 'buy';
                $buy['total'] = bcmulx($buy->amount, $buy->price, 8);
                $fees = 0;
                if ($buy->maker_fees > $buy->taker_fees) {
                    $fees = bcmulx(bcmulx(bcmulx(bcsubx($buy->amount, $buy->processed, 8), $buy->price, 8), $buy->maker_fees, 8), 0.01, 8);
                } else {
                    $fees = bcmulx(bcmulx(bcmulx(bcsubx($buy->amount, $buy->processed, 8), $buy->price, 8), $buy->taker_fees, 8), 0.01, 8);
                }
                $buy['fees'] = $fees;

                $this->orderBookWebsoketService->sendData($buy);
                $this->privateWsDataService->sendData($buy);
                event(new OrderHasPlaced($buy));

                return [
                    'status' => true,
                    'message' => __('Buy order is placed successfully!'),
                    'data' => []
                ];
            }
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place buy order !'),
            ];
        } catch (\Exception $e) {
            DBService::rollBack();
            storeException('_passiveBuyOrder exception ', $e->getMessage());
            return [
                'status' => false,
                'message' => __('Failed to place buy order !'),
            ];
        }
    }

    /**
     * Get total cost of a buy order
     * @param Request $request
     * @return string
     */
    private function _getTotalBuyCost(
        $price,
        $amount,
        $makerFees,
        $takerFees
    )
    {
        $total = bcmulx($price, $amount);
        $fees = $makerFees > $takerFees ? $makerFees : $takerFees;
        $totalWithFees = bcaddx($total, bcdivx(bcmulx($total, $fees), "100"));

        return $totalWithFees;
    }

    /**
     * Get all buy orders of order book
     * @param $base_coin_id
     * @param $trade_coin_id
     * @return mixed
     */
    public function getAllOrders($base_coin_id, $trade_coin_id)
    {
        return $this->object->getAllOrders($base_coin_id, $trade_coin_id);
    }

    public function getAllOrderHistory($userId, $search = null, $order_data = null)
    {
        return Buy::where('user_id', $userId)
            ->leftJoin('coins as bc', 'bc.id', '=', 'buys.base_coin_id')
            ->leftJoin('coins as tc', 'tc.id', '=', 'buys.trade_coin_id')
            ->where('amount', '>', 0)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('amount', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%")
                        ->orWhere('processed', 'LIKE', "%{$search}%")
                        ->orWhere('bc.coin_type', 'LIKE', "%{$search}%")
                        ->orWhere('tc.coin_type', 'LIKE', "%{$search}%");

                    $searchLower = strtolower($search);
                    if (Str::contains($searchLower, 'pending')) {
                        $q->orWhere('buys.status', STATUS_PENDING);
                    } elseif (Str::contains($searchLower, 'success')) {
                        $q->orWhere('buys.status', STATUS_ACCEPTED);
                    }
                });
            })
            ->select([
                'amount',
                'processed',
                'price',
                'buys.status',
                DB::raw("bc.coin_type as base_coin"),
                DB::raw("tc.coin_type as trade_coin"),
                DB::raw("'buy' as type"),
                'buys.created_at',
                'buys.deleted_at'
            ])
            ->when(
                $order_data && !empty($order_data['column_name']) && !empty($order_data['order_by']),
                function ($query) use ($order_data) {
                    return $query->orderBy($order_data['column_name'], $order_data['order_by']);
                },
                function ($query) {
                    return $query->orderBy('buys.created_at', 'DESC');
                }
            )
            ->withTrashed();
    }
    /**
     * Get total volume
     * @param $base_coin_id
     * @param $trade_coin_id
     * @return string
     */
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

    /**
     * Place multi buy order
     * @param $request
     * @return array
     */
    public function createMultiBuyOrder($request)
    {
        try {
            $userId = Auth::id();
            $response = false;
            // get buy wallet details
            DBService::beginTransaction();
            $walletRepository = new UserWalletRepository(UserWallet::class);
            $walletDetails = $walletRepository->getUserSingleWalletBalance($userId, $request->base_coin_id);
            if (!$walletDetails) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => 'Invalid buy order request!',
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

            $feesPercent = $request->maker_fees > $request->taker_fees ? $request->maker_fees : $request->taker_fees;
            $inputAmount1 = bcmulx($request->price_1, $request->amount_1);
            $inputTotal1 = bcaddx($inputAmount1, bcdivx(bcmulx($inputAmount1, $feesPercent), "100"));
            $inputAmount2 = bcmulx($request->price_2, $request->amount_2);
            $inputTotal2 = bcaddx($inputAmount2, bcdivx(bcmulx($inputAmount2, $feesPercent), "100"));

            if (isset($request->price_3) && !empty($request->price_3) && isset($request->price_3) && !empty($request->price_3)) {
                $inputAmount3 = bcmulx($request->price_3, $request->amount_3);
                $inputTotal3 = bcaddx($inputAmount3, bcdivx(bcmulx($inputAmount3, $feesPercent), "100"));
            } else {
                $inputTotal3 = 0;
            }

            $inputTotal = bcaddx($inputTotal1, bcaddx($inputTotal2, $inputTotal3));
            $totalBuyCost = custom_number_format($inputTotal);

            // checking if available balance is there
            if (bccompx($mainBalance, $totalBuyCost) === -1) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('You need minimum balance(including fees): ') . $totalBuyCost . ' ' . $walletDetails->coin_type,
                ];
            }
            $orders = [];
            $msg1 = $msg2 = $msg3 = "";
            $feesZero1 = $feesZero2 = $feesZero3 = 0;
            $currentTime = Carbon::now();
            if (isset($request->price_1) && !empty($request->price_1) && isset($request->amount_1) && !empty($request->amount_1)) {
                $feesZero1 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_1, 'buy', $request->price_1);
                if ($feesZero1) {
                    $msg1 = __("Buy Total (" . bcmulx($request->price_1, $request->amount_1) . ")  Should Not Less Than ") . $feesZero1;
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
                $feesZero2 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_2, 'buy', $request->price_2);
                if ($feesZero2) {
                    $msg2 = __("Buy Total (" . bcmulx($request->price_2, $request->amount_2) . ")  Should Not Less Than ") . $feesZero2;
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
                $feesZero3 = isFeesZero(Auth::id(), $request->base_coin_id, $request->trade_coin_id, $request->amount_3, 'buy', $request->price_3);
                if ($feesZero3) {
                    $msg3 = __("Buy Total (" . bcmulx($request->price_3, $request->amount_3) . ")  Should Not Less Than ") . $feesZero3;
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
            //            $response = getService(['method'=>'deductBalanceById','params'=>['user_id'=>$userId,'coin_id'=>$request->base_coin_id,'amount'=>$totalBuyCost]]);
            $response = $walletRepository->deductBalanceById($walletDetails, $totalBuyCost);
            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('Failed to place order!'),
                ];
            }

            if (isset($orders[0])) {
                if ($buy = $this->object->create($orders[0])) {

                    //broadcastOrderData($buy, 'buy', 'orderPlace');
                    storeException("MultiBuyOrderPlace", "Buy Details 1: Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");
                }
            }
            if (isset($orders[1])) {
                if ($buy = $this->object->create($orders[1])) {

                    //broadcastOrderData($buy, 'buy', 'orderPlace');
                    storeException("MultiBuyOrderPlace", "Buy Details 2: Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");
                }
            }
            if (isset($orders[2])) {
                if ($buy = $this->object->create($orders[2])) {

                    //broadcastOrderData($buy, 'buy', 'orderPlace');
                    storeException("MultiBuyOrderPlace", "Buy Details 3: Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");
                }
            }

            DBService::commit();


            //broadcastWalletData($walletDetails->wallet_id);

            return [
                'status' => true,
                'message' => __('Multi buy order is placed successfully!'),
            ];
        } catch (\Exception $e) {
            DBService::rollBack();

            return [
                'status' => false,
                'message' => __('Failed to place order!' . $e->getMessage())
            ];
        }
    }

    /**
     * Get max buy order price from order book
     * @param $baseCoinId
     * @param $tradeCoinId
     * @return mixed
     */
    public function getPrice($baseCoinId, $tradeCoinId)
    {
        return $this->object->getPrice($baseCoinId, $tradeCoinId);
    }

    /**
     * Get on order balance of an user
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
     * Get my order list
     * @param $baseCoinId
     * @param $tradeCoinId
     * @param $userId
     * @return mixed
     */
    public function getMyOrders($baseCoinId, $tradeCoinId, $userId)
    {
        return $this->object->getMyOrders($baseCoinId, $tradeCoinId, $userId);
    }

    /**
     * Get all orders
     * @return mixed
     */
    public function getOrders()
    {
        return $this->object->getOrders();
    }

    /**
     * insert and delete orderbook as favorite
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
                'type' => DB::raw("'buy'")
            ])->first();
            if (is_null($obj)) {

                $isOrder = Buy::where([
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
                    'type' => 'buy',
                    'user_id' => DB::raw(Auth::id())
                ]);

                //broadcastPrivate( 'isFavoriteOrderBook', ['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price, 'type' => 'buy', 'action' => 'add'], Auth::id());
                return response()->json([
                    'status' => true,
                    'message' => __('add.to.favorite')
                ]);
            } else {
                $obj->delete();

                //broadcastPrivate('isFavoriteOrderBook', ['base_coin_id' => $request->base_coin_id, 'trade_coin_id' => $request->trade_coin_id, 'price' => $request->price, 'type' => 'buy', 'action' => 'remove'], Auth::id());
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

    /**
     * Place bot buy order
     * @param Request $request
     * @return array
     */
    public function botOrderCreate(Request $request)
    {
        storeBotException('botOrderCreate buy', date('Y-m-d H:i:s'));
        $request->merge(['is_bot' => 0]);
        $this->orderService->placeOrder( OrderCreationDto::fromRequest($request), 'buy');
    }

    /**
     * Place new bot buy order 12/10/23
     * @param Request $request
     * @return array
     */

    public function createNewBotOrder($orderInfo, $pair, $user)
    {
        try {
            storeBotException('buy pair => ', $pair->pair_bin);
            storeBotException('buy order info => ', $orderInfo);
            storeBotException('createNewBotOrder amount => ', $orderInfo['amount']);
            storeBotException('createNewBotOrder price => ', $orderInfo['price']);
            $amount = cleanAndConvertToDecimal($orderInfo['amount']);
            storeBotException('amount => ', $amount);
            $price = cleanAndConvertToDecimal($orderInfo['price']);
            storeBotException('price => ', $price);
            $userId = $user;
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
            storeBotException('buy order => ', $order);
            if ($buy = $this->object->create($order)) {

                storeBotException("bot BuyOrderPlace", "Buy Id: $buy->id Price: $buy->price Amount: $buy->amount");

                $requestData = [
                    'dashboard_type' => 'dashboard',
                    'order_type' => 'buy',
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
                // storeBotException('sendDataThroughWebSocket bot socket data', $socket_data);

                event(new OrderHasPlaced($buy));

                return [
                    'success' => true,
                    'message' => __('Bot Buy order is placed successfully!'),
                    'data' => []
                ];
            }
        } catch (\Exception $e) {
            storeBotException('createNewBotOrder buy', $e->getMessage());
        }
    }

    public function getAllBuyOrders(int $baseCoinId, int $tradeCoinId, int $userId, int $limit = 25)
    {
        return $this->object->getAllBuyOrders($baseCoinId, $tradeCoinId, $userId, $limit);
    }
}
