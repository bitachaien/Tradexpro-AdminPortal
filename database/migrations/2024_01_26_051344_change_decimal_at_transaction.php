<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDecimalAtTransaction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimate_gas_fees_transaction_histories', function (Blueprint $table) {
            $table->decimal("amount",29,18)->default(0)->change();
            $table->decimal("fees",29,18)->default(0)->change();
        });
        Schema::table('withdraw_histories', function (Blueprint $table) {
            $table->decimal("used_gas",29,18)->default(0)->change();
        });
        Schema::table('admin_receive_token_transaction_histories', function (Blueprint $table) {
            $table->decimal("amount",29,18)->default(0)->change();
            $table->decimal("fees",29,18)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimate_gas_fees_transaction_histories', function (Blueprint $table) {
            //
        });
        Schema::table('withdraw_histories', function (Blueprint $table) {
            //
        });
        Schema::table('admin_receive_token_transaction_histories', function (Blueprint $table) {
            //
        });
    }
}
