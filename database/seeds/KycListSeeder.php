<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\KycList;

class KycListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (kycList() as $key => $value) {
            KycList::firstOrCreate(['type' => $key], ['name' => $value]);
        }
    }
}
