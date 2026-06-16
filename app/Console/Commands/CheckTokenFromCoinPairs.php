<?php

namespace App\Console\Commands;

use App\Model\CoinPair;
use Illuminate\Console\Command;

class CheckTokenFromCoinPairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:token-coin-pair';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check if the coin pair is for native coin or token, if token then make coin pair is_token=1';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $coinPairs = CoinPair::orderBy('id', 'asc')->get();
        if(isset($coinPairs[0])) {
            foreach($coinPairs as $pair) {
                $isToken = checkNetworkCoinPrice($pair->parent_coin_id,$pair->child_coin_id);
                if ($isToken) {
                    $pair->update(['is_token' => STATUS_ACTIVE]);
                }
            }
        }
    }
}
