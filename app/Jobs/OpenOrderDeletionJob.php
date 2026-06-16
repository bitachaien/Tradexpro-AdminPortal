<?php

namespace App\Jobs;

use App\Contracts\Repositories\ClosableOrderRepository;
use App\Http\Services\SpotOrderDeletionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OpenOrderDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $queue = 'trade-processor';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private SpotOrderDeletionService $spotOrderDeletionService,
        private ClosableOrderRepository $orderRepository,
        private int $id,
        private int $userId,
        private string $type
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->spotOrderDeletionService->deleteOpenOrder(
            $this->orderRepository,
            $this->id,
            $this->userId,
            $this->type
        );
    }
}
