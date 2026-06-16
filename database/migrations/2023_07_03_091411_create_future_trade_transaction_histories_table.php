<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFutureTradeTransactionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_trade_transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('future_wallet_id')->nullable();
            $table->unsignedBigInteger('coin_pair_id')->nullable();
            $table->tinyInteger('type')->nullable();
            $table->decimal('amount', 19, 8)->default(0);
            $table->string('coin_type')->nullable();
            $table->string('symbol')->nullable();
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
        Schema::dropIfExists('future_trade_transaction_histories');
    }
}
