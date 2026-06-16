<?php

namespace App\Listeners;

use App\Contracts\Repositories\Factories\OrderRepositoryFactoryInterface;
use App\Dtos\OrderProcessingDTO;
use App\Events\OrderHasPlaced;
use App\Http\Services\BuySellTransactionService;
use App\Http\Services\TradeServices\TradeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class StartProcessingOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'trade-processor';

    const OPPOSITE_ORDER_TYPE = [
        'buy' => 'sell',
        'sell' => 'buy',
    ];

    const PRICE_COMPARATOR = [
        'buy' => '<=',
        'sell' => '>=',
    ];

    const ORDER_BY_DIRECTION = [
        'buy' => 'asc',
        'sell' => 'desc',
    ];

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private BuySellTransactionService $tradeService,
        private TradeService $tradeProcessor,
        private OrderRepositoryFactoryInterface $orderRepositoryFactory
    ) {}

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderHasPlaced $event)
    {
        try {
            $type = strtolower(Str::singular($event->order->getTable()));
            $orderRepository = $this->orderRepositoryFactory->getOrderRepositoryByType(self::OPPOSITE_ORDER_TYPE[$type]);

            foreach ($orderRepository->getMatchedOrders(
                $event->order->base_coin_id,
                $event->order->trade_coin_id,
                $event->order->is_market,
                $event->order->price,
                self::ORDER_BY_DIRECTION[$type],
                self::PRICE_COMPARATOR[$type]
            ) as $matchedOrders) {
                $shouldProcessNextChunk = $this->processChunkedOrders(
                    $matchedOrders,
                    $event,
                    $type
                );

                if (! $shouldProcessNextChunk) {
                    break;
                }
            }

            if ($event->order->is_market == 1) {

                $order = $this->orderRepositoryFactory->getOrderRepositoryByType($type)
                    ->getById($event->order->id);

                if (! $order) {
                    return;
                }

                $this->tradeService->closeOrder(
                    $order,
                    $type
                );
            }

        } catch (\Exception $e) {
            storeException('Event Error', $e);
        }
    }

    private function processChunkedOrders($matchedOrders, $event, $type): bool
    {
        foreach ($matchedOrders as $matchedOrder) {

            $shouldProcessNextMatchedOrders = $this->tradeProcessor->process(new OrderProcessingDTO(
                $event->order->id,
                $matchedOrder->id,
                $type,
            ));

            if (! $shouldProcessNextMatchedOrders) {
                return false;
            }
        }

        return true;
    }
}
