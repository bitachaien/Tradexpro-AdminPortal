<?php

use App\Model\Coin;
use App\Model\Wallet;
use App\User;
use Illuminate\Database\Seeder;

class WalletTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coins = Coin::all();
        $users = User::where(['status' => STATUS_ACTIVE, 'super_admin' => STATUS_INACTIVE])->get();

        foreach ($coins as $coin) {
            foreach ($users as $user) {
                Wallet::firstOrCreate(['user_id' => $user->id, 'coin_id' => $coin->id], [
                    'name' => $coin->coin_type . ' wallet',
                    'coin_type' => $coin->coin_type
                ]);
            }
        };
    }
}
