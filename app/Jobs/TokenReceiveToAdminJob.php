<?php

namespace App\Jobs;

use App\Http\Repositories\CustomTokenRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class TokenReceiveToAdminJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    private $transactions;
    private $adminId;
    public function __construct($transactions,$adminId)
    {
        $this->transactions =  $transactions;
        $this->adminId =  $adminId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tokenRepo = new CustomTokenRepository();
        try {
            $tokenRepo->tokenReceiveManuallyByAdminFromBuyToken($this->transactions,$this->adminId);
        } catch (\Exception $e) {
            storeException('PendingDepositAcceptJob', $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        storeException('TokenReceiveToAdminJob',json_encode($exception));
        // Send user notification of failure, etc...
    }
}
