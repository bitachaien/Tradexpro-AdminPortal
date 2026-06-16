<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPkAtWalletAddressHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            $table->text('wallet_key')->nullable()->after('coin_type');
        });
        Schema::table('withdraw_histories', function (Blueprint $table) {
            $table->decimal('used_gas',19,8)->default(0)->after('coin_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            //
        });
        Schema::table('withdraw_histories', function (Blueprint $table) {
            //
        });
    }
}
