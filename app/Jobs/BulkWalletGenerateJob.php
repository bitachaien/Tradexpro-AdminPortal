<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Http\Services\WalletService;
use App\Traits\ResponseHandlerTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BulkWalletGenerateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResponseHandlerTrait;

    public function __construct(protected $id, protected $type)
    {
    }

    public function handle()
    {
        return $this->handlerGeneralResponse(function () {
            return (new WalletService())->bulkWalletGenerate($this->id, $this->type);
        });
    }
}
