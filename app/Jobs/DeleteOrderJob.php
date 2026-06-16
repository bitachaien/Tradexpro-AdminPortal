<?php

namespace App\Jobs;

use App\Http\Services\OrderDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $request, private int $userId)
    {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderDeletionService $orderDeletionService)
    {
        $orderDeletionService->deleteOrder(new Request($this->request), $this->userId);
    }
}
