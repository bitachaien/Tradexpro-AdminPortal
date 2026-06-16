<?php

namespace App\Jobs;

use App\Contracts\OrderServiceInterface;
use App\Dtos\OrderCreationDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PlaceOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private OrderServiceInterface $orderService,
        private OrderCreationDto $request,
        private bool $isMarket,
        private string $type
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->orderService->placeOrder($this->request, $this->type);
    }
}
