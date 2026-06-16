<?php

namespace Database\Seeders;

use App\Model\CoinPair;
use App\Model\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseVolumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coinPairWithBaseVolume = CoinPair::where('base_volume', '>', 0)
            ->first();
        
        if($coinPairWithBaseVolume) {
            return;
        }
        
        $coinPairs = CoinPair::all();
       
        foreach($coinPairs as $coinPair) {
            $transaction = Transaction::select(DB::raw('sum(amount * price) as volume'))
                ->where('base_coin_id', $coinPair->parent_coin_id)
                ->where('trade_coin_id', $coinPair->child_coin_id)
                ->where('created_at', '>', Carbon::now()->subDays(1))
                ->groupBy(['base_coin_id', 'trade_coin_id'])
                ->first();

            if(! $transaction) {
                continue;
            }

            $coinPair->base_volume = $transaction->volume;
            $coinPair->save();
        }
    }
}
