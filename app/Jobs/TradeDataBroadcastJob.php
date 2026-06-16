<?php

namespace App\Jobs;

use App\Http\Services\WebsocketServices\OrderBookWebsoketService;
use App\Http\Services\WebsocketServices\OtherSpotTradeWebsocketService;
use App\Http\Services\WebsocketServices\PrivateWsDataService;
use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TradeDataBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private ?Model $order,
        private ?Model $matchedOrder
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        OrderBookWebsoketService $orderBookWebsoketService,
        OtherSpotTradeWebsocketService $otherSpotTradeWebsocketService,
        PrivateWsDataService $privateWsDataService
    )
    {
        if (isset($this->order)) {
            $otherSpotTradeWebsocketService->sendData($this->order);
            $orderBookWebsoketService->sendData($this->order);
            $privateWsDataService->sendData($this->order);
        }

        if (isset($this->matchedOrder)) {
            $privateWsDataService->sendData($this->matchedOrder);
        }
    }
}
