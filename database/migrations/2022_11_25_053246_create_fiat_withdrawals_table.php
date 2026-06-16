<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiatWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fiat_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('bank_id')->unsigned();
            $table->bigInteger('wallet_id')->unsigned();
            $table->bigInteger('admin_id')->nullable();
            $table->string('currency',30);
            $table->decimal('coin_amount',19,8)->default(0);
            $table->decimal('currency_amount',19,8)->default(0);
            $table->decimal('rate',19,8)->default(0);
            $table->decimal('fees',19,8)->default(0);
            $table->string("bank_slip")->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fiat_withdrawals');
    }
}
