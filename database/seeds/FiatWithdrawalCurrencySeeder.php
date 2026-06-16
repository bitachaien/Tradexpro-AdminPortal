<?php

//namespace Database\Seeders;

use App\Model\CurrencyList;
use App\Model\FiatWithdrawalCurrency;
use Illuminate\Database\Seeder;

class FiatWithdrawalCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencyList = CurrencyList::where(['status' => STATUS_ACTIVE])->limit(2)->get();
        if (isset($currencyList[0])) {
            foreach ($currencyList as $item) {
                FiatWithdrawalCurrency::firstOrCreate(['currency_id' => $item->id],['status' => STATUS_ACTIVE]);
            }
        }

    }
}
