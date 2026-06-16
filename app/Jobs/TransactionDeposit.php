<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Http\JsonResponse;
use App\Traits\ResponseHandlerTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Http\Services\TransactionDepositService;

class TransactionDeposit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResponseHandlerTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected $data)
    {
        //
    }

    public function handle(): JsonResponse
    {
        return $this->handlerApiResponse(function () {
            return (new TransactionDepositService())->checkAddressAndDeposit($this->data);
        });

    }
}
