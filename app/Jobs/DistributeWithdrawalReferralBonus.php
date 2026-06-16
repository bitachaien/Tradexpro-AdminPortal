<?php

namespace App\Jobs;

use App\Http\Repositories\AffiliateRepository;
use App\Http\Services\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DistributeWithdrawalReferralBonus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $transaction;
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            storeBotException('DistributeWithdrawalReferralBonus', 'called');
            $repo = new AffiliateRepository();
            $repo->storeAffiliationHistory($this->transaction);
            storeBotException('DistributeWithdrawalReferralBonus', 'executed');
        } catch (\Exception $e) {
            storeException('DistributeWithdrawalReferralBonus', $e->getMessage());
        }
    }
}
