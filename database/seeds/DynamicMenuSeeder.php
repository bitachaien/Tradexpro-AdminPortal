<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Model\DynamicMenu;

class DynamicMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DynamicMenu::firstOrCreate(['name'=>'Exchange']);
    }
}
