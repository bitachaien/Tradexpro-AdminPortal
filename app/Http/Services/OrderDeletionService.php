<?php

namespace App\Http\Services;

use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Model\Buy;
use App\Model\Sell;
use App\Model\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderDeletionService
{
    public function __construct(
        private OrderBookWebsoketService $orderBookWebsoketService,
        private PrivateWsDataService $privateWsDataService
    ) {}

    public function deleteOrder($request, int $userId)
    {
        DBService::beginTransaction();
        try {
            $type = $request->type;
            $id = $request->id;
            $service = null;
            if ($type == 'buy') {
                $service = new BuyOrderService();
            } elseif ($type == 'sell') {
                $service = new SellOrderService();
            } else {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('invalid order type')
                ];
            }

            $order = $service->getDocs(['id' => $id, 'user_id' => $userId, 'status' => 0])->first();
            if (empty($order)) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('no order found')
                ];
            }
            $base_coin_id = $order->base_coin_id;
            $trade_coin_id = $order->trade_coin_id;
            $restAmount = bcsubx($order->amount, $order->processed);
            $walletRepository = new UserWalletRepository(Wallet::class);

            if ($type == 'buy') {
                $fees = $order->maker_fees > $order->taker_fees ? $order->maker_fees : $order->taker_fees;
                $total = bcmulx($restAmount, $order->price);
                $returnAmount = bcaddx($total, bcdivx(bcmulx($total, $fees), "100"));
                $wallet = $walletRepository->getDocs(['user_id' => $userId, 'coin_id' => $order->base_coin_id])->first();
                $response = $walletRepository->addBalanceById($userId, $order->base_coin_id, $returnAmount);
                $orderService = new BuyOrderRepository(Buy::class);
            } else {
                $wallet = $walletRepository->getDocs(['user_id' => $userId, 'coin_id' => $order->trade_coin_id])->first();
                $response = $walletRepository->addBalanceById($userId, $order->trade_coin_id, $restAmount);
                $orderService = new SellOrderRepository(Sell::class);
            }

            if ($response == false) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('something went wrong')
                ];
            }
            $isDeleteOrUpdate = false;
            if ($order->processed > 0) {
                $isDeleteOrUpdate = $orderService->updateWhere(['id' => $order->id, 'user_id' => $userId, 'status' => 0], ['status' => 1, 'amount' => $order->processed]);
            } else {
                $isDeleteOrUpdate = $orderService->deleteWhere(['id' => $order->id, 'user_id' => $userId, 'processed' => 0, 'status' => 0, 'deleted_at' => null]);
            }
            if (!$isDeleteOrUpdate) {
                DBService::rollBack();
                return [
                    'status' => false,
                    'message' => __('no order found')
                ];
            }
            DBService::commit();
        } catch (\Exception $exception) {
            DBService::rollBack();
            return [
                'status' => false,
                'message' => __('something went wrong') . $exception->getMessage() . $exception->getLine()
            ];
        }

        $this->orderBookWebsoketService->sendData($order);
        $this->privateWsDataService->sendData($order);

        return [
            'status' => true,
            'message' => __('order deleted successfully'),
        ];
    }
}
