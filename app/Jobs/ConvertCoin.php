<?php

namespace App\Jobs;

use App\Model\WalletSwapHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class ConvertCoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $input;
    private $fromWallet;
    private $toWallet;
    public function __construct($input,$fromWallet,$toWallet)
    {
        $this->input = $input;
        $this->fromWallet = $fromWallet;
        $this->toWallet = $toWallet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            if($this->fromWallet->balance < $this->input['requested_amount']) {
                $data = ['success' => false, 'message' => __("Wallet hasn't enough balance")];
                storeException('ConvertCoin',"Wallet hasn't enough balance");
                return 0;
            }
            if ($this->fromWallet->coin_type == $this->toWallet->coin_type){
                $data = ['success' => false, 'message' => __('Can not swap to same wallet')];
                storeException('ConvertCoin',"Can not swap to same wallet");
                return 0;
            }
            WalletSwapHistory::create($this->input);

            $this->fromWallet->decrement('balance',$this->input['requested_amount']);
            $this->toWallet->increment('balance',$this->input['converted_amount']);

            storeException('ConvertCoin','Coin Converted successful');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            storeException('coin convert job exception ',$e->getMessage());
        }
    }
}
