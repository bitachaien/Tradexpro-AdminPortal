<?php

namespace Database\Seeders;

use App\Model\Faq;
use App\Model\UserNavbar;
use Illuminate\Database\Seeder;

class UserNavbarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [ 'title' => '', 'slug' => 'login', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'signUp', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'trade', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'wallet', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'ico', 'sub' => 0, 'main_id' => NULL ],

            [ 'title' => '', 'slug' => 'fiat', 'sub' => 1, 'main_id' => NULL ],
                [ 'title' => '', 'slug' => 'deposit', 'sub' => 0, 'main_id' => 6 ],
                [ 'title' => '', 'slug' => 'withdrawal', 'sub' => 0, 'main_id' => 6 ],

            [ 'title' => '', 'slug' => 'reports', 'sub' => 1, 'main_id' => NULL ],
                [ 'title' => '', 'slug' => 'depositHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'withdrawalHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'swapHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'buyOrderHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'sellOrderHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'transactionHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'fiatDepositHistory', 'sub' => 0, 'main_id' => 9 ],
                [ 'title' => '', 'slug' => 'fiatWithdrawalHistory', 'sub' => 0, 'main_id' => 9 ],

            [ 'title' => '', 'slug' => 'myProfile', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'myReferral', 'sub' => 0, 'main_id' => NULL ],
            [ 'title' => '', 'slug' => 'settings', 'sub' => 1, 'main_id' => NULL ],
                [ 'title' => '', 'slug' => 'mySettings', 'sub' => 0, 'main_id' => 20 ],
                [ 'title' => '', 'slug' => 'faq', 'sub' => 0, 'main_id' => 20 ],
        ];

        UserNavbar::truncate()->insert($data);
    }
}
