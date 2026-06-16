<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErc20InfoAtCoinSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_settings', function (Blueprint $table) {
            $table->string('contract_coin_name')->nullable()->after('coin_api_user');
            $table->string('chain_link')->nullable()->after('coin_api_user');
            $table->string('chain_id')->nullable()->after('coin_api_user');
            $table->string('contract_address')->nullable()->after('coin_api_user');
            $table->string('wallet_address')->nullable()->after('coin_api_user');
            $table->string('wallet_key')->nullable()->after('coin_api_user');
            $table->string('contract_decimal')->nullable()->after('coin_api_user');
            $table->integer('gas_limit')->nullable()->after('coin_api_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_settings', function (Blueprint $table) {
            //
        });
    }
}
