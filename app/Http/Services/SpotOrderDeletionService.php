<?php

namespace App\Http\Services;

use App\Contracts\Repositories\ClosableOrderRepository;
use App\Contracts\Repositories\TradableOrderRepositoryInterface;
use App\Dtos\ClosebleOrderDto;
use App\Exceptions\InvalidOrderTypeException;
use App\Exceptions\OrderNotFoundException;
use App\Http\Repositories\Factories\ClosebleOrderRepositoryFactory;
use App\Http\Repositories\Factories\OrderRepositoryFactory;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Jobs\OpenOrderDeletionJob;
use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SpotOrderDeletionService
{
    public function __construct(
        private OrderBookWebsoketService $orderBookWebsoketService,
        private PrivateWsDataService $privateWsDataService,
        private ClosebleOrderRepositoryFactory $orderRepositoryFactory,
        private UserWalletRepository $userWalletRepository
    ) {}

    public function checkAndDeleteOrder(int $id, int $userId, string $type)
    {
        if (! ($type == 'buy' || $type == 'sell' || $type == 'stop')) {
            throw new InvalidOrderTypeException('Invalid order type '.$type);
        }

        $orderRepository = $this->orderRepositoryFactory->getOrderRepository($type);

        $order = $orderRepository->findByIdStatusAndUserId($id, $userId, 0);
        if (! $order) {
            throw new OrderNotFoundException('Order not found');
        }

        OpenOrderDeletionJob::dispatch(
            $this,
            $orderRepository,
            $id,
            $userId,
            $type
        );
    }

    public function deleteOpenOrder(
        ClosableOrderRepository $orderRepository,
        int $id,
        int $userId,
        string $type
    ) {
        try {
            DB::beginTransaction();
            $order = $orderRepository->findByIdStatusUserIdAndLock($id, $userId, 0);
            if (! $order) {
                DB::rollBack();
                throw new OrderNotFoundException('Order not found');
            }

            $returnAmount = bcsub($order->amount, $order->processed);

            if ($order->type == 'buy') {
                $returnAmount = $this->getReturnAmount($order, $returnAmount);
            }

            $coinId = $this->getCoinId($order);
            
            Log::info($type);
            Log::info($returnAmount);
            Log::info($coinId);

            $this->userWalletRepository->addBalanceById($userId, $coinId, $returnAmount);
            $orderRepository->closeOrder($order->id);
            DB::commit();

            $this->orderBookWebsoketService->sendData($order);
            $this->privateWsDataService->sendData($order);
        } catch (\Throwable $exception) {
            DB::rollBack();
            storeException('Order deletion', $exception->getMessage());
            throw $exception;
        }

    }

    private function getReturnAmount(ClosebleOrderDto $order, $returnAmount)
    {
        $fees = $order->maker_fees > $order->taker_fees ? $order->maker_fees : $order->taker_fees;
        $total = bcmul($returnAmount, $order->price);
        $returnAmount = bcadd($total, bcdiv(bcmul($total, $fees), '100'));

        return $returnAmount;
    }

    private function getCoinId(ClosebleOrderDto $order): int
    {
        return $order->type === 'buy' ? $order->base_coin_id : $order->trade_coin_id;
    }
}
