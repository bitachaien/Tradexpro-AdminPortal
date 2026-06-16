<?php

namespace App\Jobs;

use App\Contracts\Repositories\OrderCoinPairRepositoryInterface;
use App\Http\Services\StopLimitService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StopLimitProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param $coinPair
     */
    public function __construct(private int $coinPairId)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(
        StopLimitService $stopLimitService,
        OrderCoinPairRepositoryInterface $orderCoinPairRepository
    )
    {
        $coinPair = $orderCoinPairRepository->findById($this->coinPairId);

        if(! $coinPair) {
            return;
        }

        $stopLimitService->process($coinPair);
    }
}
