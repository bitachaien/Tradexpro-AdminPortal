<?php

namespace App\Services\Order;

use App\Contracts\OrderServiceInterface;
use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderCreationDto;
use App\Events\OrderHasPlaced;
use App\Exceptions\OrderException;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\DBService;
use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Jobs\PlaceOrderJob;
use App\Model\Buy;
use App\Model\Sell;
use Throwable;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private UserWalletRepository $walletRepository,
        private OrderCostService $orderCostService,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory,
        private OrderBookWebsoketService $orderBookWebsoketService,
        private PrivateWsDataService $privateWsDataService,
        private OrderValidationService $orderValidationService
    ) {}

    public function checkAndCreateOrder(OrderCreationDto $data, string $type)
    {
        $this->orderValidationService->validateOrder($data, $type);
        PlaceOrderJob::dispatch(
            $this,
            $data,
            false,
            $type
        )->onQueue('balance-debit');
    }

    public function placeOrder(OrderCreationDto $data, string $type)
    {
        $order = $this->createOrderAndDeductBalance($data, $type);
        $this->orderBookWebsoketService->sendData($order);
        $this->privateWsDataService->sendData($order);
        event(new OrderHasPlaced($order));
    }

    private function createOrderAndDeductBalance(OrderCreationDto $data, string $type): Buy|Sell
    {
        $orderRepository = $this->orderRepositoryFactory->getOrderRepositoryByType($type);

        $totalCost = $this->orderCostService->getTotalCost(
            $data->price,
            $data->amount,
            $data->maker_fees,
            $data->taker_fees,
            $type
        );

        DBService::beginTransaction();

        try {
            $walletDetails = $this->walletRepository->getWalletAndLock(
                $data->user_id,
                $type == 'buy' ? $data->base_coin_id : $data->trade_coin_id
            );

            if (! $walletDetails) {
                throw new OrderException(__('Invalid '.$type.' order request!'));
            }

            $mainBalance = $walletDetails->balance;

            if (bccomp($mainBalance, $totalCost) === -1) {
                throw new OrderException(__('You need minimum balance(including fees): ').$totalCost.' '.$walletDetails->coin_type);
            }

            $response = $this->walletRepository->deductBalanceById($walletDetails, $totalCost);
            if ($response == false) {
                throw new OrderException('Failed to deduct balance');
            }

            $order = $orderRepository->create($data);

            DBService::commit();

            return $order;
        } catch (Throwable $e) {
            DBService::rollBack();
            storeException('Order ex ', $e->getMessage());
            throw $e;
        }
    }
}
