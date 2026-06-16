<?php

namespace App\Jobs;

use App\Http\Repositories\CustomTokenRepository;
use App\Model\DepositeTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

class PendingDepositAcceptJob implements ShouldQueue
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
        storeBotException('PendingDepositAcceptJob for '.$this->transactions->coin_type, 'called');
        $tokenRepo = new CustomTokenRepository();
        try {
            if (!empty($this->transactions)) {
                $tokenRepo->tokenReceiveManuallyByAdmin($this->transactions,$this->adminId);
            } else {
                storeBotException('PendingDepositAcceptJob', 'deposit list not found');
            }
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
        storeException('PendingDepositAcceptJob',json_encode($exception));
        // Send user notification of failure, etc...
    }
}
