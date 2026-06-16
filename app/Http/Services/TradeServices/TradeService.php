<?php

namespace App\Http\Services\TradeServices;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderProcessingDTO;
use App\Http\Services\BuySellTransactionService;
use App\Jobs\TradeDataBroadcastJob;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TradeService
{
    const OPPOSITE_ORDER_TYPE = [
        'buy' => 'sell',
        'sell' => 'buy',
    ];

    public function __construct(
        private FeeCheckerAndRefundService $feeCheckerAndRefundService,
        private BuySellTransactionService $buySellTransactionService,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory,
    ) {}

    public function process(OrderProcessingDTO $data): bool
    {
        if (! array_key_exists($data->type, self::OPPOSITE_ORDER_TYPE)) {
            storeBotException('Trade Processor', 'Invalid type '.$data->type);
            throw new InvalidArgumentException('Invalid type '.$data->type);
        }

        $orderRepository = $this->orderRepositoryFactory
            ->getOrderRepositoryByType($data->type);

        $order = $orderRepository
            ->getById($data->orderId);
        $matchedOrder = $this->orderRepositoryFactory
            ->getOrderRepositoryByType(self::OPPOSITE_ORDER_TYPE[$data->type])
            ->getById($data->matchedOrderId);

        if (! $order || $order->status == 1 || $order->amount == $order->processed) {
            return false;
        }

        if (! $this->feeCheckerAndRefundService
            ->shouldProcessOrder($orderRepository, $order->id, $data->type, $order->price)
        ) {
            return false;
        }

        if ($this->shouldProcessOrder($order, $matchedOrder, $data->type)) {
            $this->buySellTransactionService->order($order, $matchedOrder, $data->type);
        }

        TradeDataBroadcastJob::dispatch($order, $matchedOrder)->onQueue('trade-data-broadcast');

        return true;
    }

    public function shouldProcessOrder(?Model $order, ?Model $matchedOrder, string $type): bool
    {
        $matchedOrderRepository = $this->orderRepositoryFactory
            ->getOrderRepositoryByType(self::OPPOSITE_ORDER_TYPE[$type]);

        return
            isset($order)
            && isset($matchedOrder)
            && $order->status == 0
            && $matchedOrder->status == 0
            && $this->feeCheckerAndRefundService
                ->shouldProcessOrder($matchedOrderRepository, $matchedOrder->id, self::OPPOSITE_ORDER_TYPE[$type], $matchedOrder->price);
    }
}
