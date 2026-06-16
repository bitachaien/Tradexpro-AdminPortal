<?php

namespace App\Jobs;

use App\Dtos\OrderProcessingDTO;
use App\Http\Services\TradeServices\TradeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMatchedOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private OrderProcessingDTO $data,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(TradeService $tradeService)
    {
        $tradeService->process($this->data);   
    }
}
