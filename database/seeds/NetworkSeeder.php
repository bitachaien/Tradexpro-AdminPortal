<?php

use App\Model\Coin;
use App\Model\CoinSetting;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coins = Coin::all();
        
        foreach($coins as $coin) {
            CoinSetting::where('coin_id', $coin->id)
            ->whereNull('network')
            ->update(['network' => $coin->network]);
        };
    }
}
