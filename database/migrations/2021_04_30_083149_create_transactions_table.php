<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('transaction_id', 30)->nullable();
            $table->integer('base_coin_id')->unsigned();
            $table->integer('trade_coin_id')->unsigned();
            $table->bigInteger('buy_id')->unsigned()->comment('exchange_buy_id');
            $table->bigInteger('sell_id')->unsigned()->comment('exchange_sell_id');
            $table->bigInteger('buy_user_id')->unsigned();
            $table->bigInteger('sell_user_id')->unsigned();
            $table->string('price_order_type')->nullable();
            $table->decimal('amount', 19, 8)->unsigned();
            $table->decimal('price', 19, 8)->unsigned();
            $table->decimal('last_price', 19, 8)->unsigned()->nullable();
            $table->decimal('btc_rate', 19, 8)->unsigned();
            $table->decimal('btc', 19, 8)->unsigned();
            $table->decimal('total', 29, 18)->unsigned();
            $table->decimal('buy_fees', 29, 18)->unsigned();
            $table->decimal('sell_fees', 29, 18)->unsigned();
            $table->boolean('remove_from_chart')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('transactions');
    }
}
