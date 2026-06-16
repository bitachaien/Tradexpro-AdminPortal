<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeAtCoinSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_settings', function (Blueprint $table) {
            $table->text('wallet_key')->nullable()->change();
            $table->text('coin_api_pass')->nullable()->change();
            $table->text('bitgo_wallet')->nullable()->change();
            $table->tinyInteger('check_encrypt')->default(0);
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
