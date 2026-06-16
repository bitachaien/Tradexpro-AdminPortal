<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToCurrencyDepositPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currency_deposit_payment_methods', function (Blueprint $table) {
            $table->string('type',30)->default('fiat-deposit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currency_deposit_payment_methods', function (Blueprint $table) {
            $table->dropColumn("type");
        });
    }
}
