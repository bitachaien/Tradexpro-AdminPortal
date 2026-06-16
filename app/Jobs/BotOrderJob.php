<?php

namespace App\Jobs;

use App\Dtos\BotCoinPairDto;
use App\Http\Services\CacheService;
use App\Http\Services\TradingBotService;
use App\Services\TradingBotServices\BotOrderService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BotOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private BotCoinPairDto $data,
        private int $adminId
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        TradingBotService $tradingBotService,
        CacheService $cacheService
    ) {
        storeBotException('BotOrderJob running at', date('Y-m-d H:i:s'));
        
        $tradingBotService->processSinglePairBotOrder($this->adminId, $this->data);
        storeBotException('BotOrderJob end at', date('Y-m-d H:i:s'));
        $cacheService->set('bot_order_place_time_for_coin_pair_'. $this->data->id, Carbon::now());
        $cacheService->forget('bot_order_status_' . $this->data->id);

        BotOrderRemoverJob::dispatch($this->data->base_coin_id, $this->data->trade_coin_id)
            ->onQueue('trade-processor');
    }
}
