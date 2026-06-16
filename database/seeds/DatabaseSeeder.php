<?php

use Illuminate\Database\Seeder;
use Database\Seeders\LangSeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\KycListSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\CustomPageSeeder;
use Database\Seeders\ThemeColorSeeder;
use Database\Seeders\UserNavbarSeeder;
use Database\Seeders\PermissionFromDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(AdminSettingTableSeeder::class);
        $this->call(CoinSeeder::class);
        $this->call(CoinPairSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(CountrySeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(CustomPageSeeder::class);
        $this->call(LangSeeder::class);
        $this->call(KycListSeeder::class);
        $this->call(FiatWithdrawalCurrencySeeder::class);
        $this->call(PermissionFromDataSeeder::class);
        $this->call(UserNavbarSeeder::class);
        $this->call(ThemeColorSeeder::class);
        
        $this->call(NetworkSeeder::class);
        $this->call(WalletTableSeeder::class);
    }
}
