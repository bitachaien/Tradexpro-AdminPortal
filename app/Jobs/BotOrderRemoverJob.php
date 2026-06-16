<?php

namespace App\Jobs;

use App\Services\TradingBotServices\BotOpenOrderDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BotOrderRemoverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private int $baseCoinId,
        private int $tradeCoinId
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        BotOpenOrderDeletionService $botOpenOrderDeletionService
    ) {
        $types = [
            'buy',
            'sell',
        ];

        foreach ($types as $type) {
            $botOpenOrderDeletionService->closeOpenBotOrders(
                $type,
                $this->baseCoinId,
                $this->tradeCoinId
            );
        }
    }
}
