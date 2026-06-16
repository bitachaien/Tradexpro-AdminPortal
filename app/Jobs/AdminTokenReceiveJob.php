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

class AdminTokenReceiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    private $coin;
    private $sendAmount;
    private $userPId;
    private $transaction;
    private $adminId;
    public function __construct($coin,$transaction, $sendAmount, $userPId,$adminId)
    {
        $this->coin = $coin;
        $this->sendAmount = $sendAmount;
        $this->userPId = $userPId;
        $this->transaction = $transaction;
        $this->adminId = $adminId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        storeBotException('AdminTokenReceiveJob', 'job called');
        $tokenRepo = new CustomTokenRepository();
        $myTransaction = $this->transaction;
        $receiveToken = $tokenRepo->receiveTokenFromUserAddressByAdminPanel($this->coin,$myTransaction->address, $this->sendAmount, $this->userPId, $myTransaction->id);
        if ($receiveToken['success'] == true) {
            $tokenRepo->updateUserWalletByAdmin($myTransaction, $this->adminId);
        } else {
            storeException('AdminTokenReceiveJob', 'token received process failed');
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
        storeException('AdminTokenReceiveJob',json_encode($exception));
        // Send user notification of failure, etc...
    }
}
