<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFutureTradeLongShortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_trade_long_shorts', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->tinyInteger('side')->default(1);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('base_coin_id');
            $table->unsignedBigInteger('trade_coin_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->decimal('entry_price', 19,8)->default(0);
            $table->decimal('exist_price', 19,8)->default(0);
            $table->decimal('price', 19, 8)->default(0);
            $table->decimal('avg_close_price', 19, 8)->default(0);
            $table->decimal('pnl', 19, 8)->default(0);
            $table->decimal('amount_in_base_coin', 19,8)->default(0);
            $table->decimal('amount_in_trade_coin', 19,8)->default(0);
            $table->decimal('take_profit_price', 19,8)->default(0);
            $table->decimal('stop_loss_price', 19,8)->default(0);
            $table->decimal('liquidation_price', 19,8)->default(0);
            $table->decimal('margin', 19,8)->default(0);
            $table->decimal('fees', 19,8)->default(0);
            $table->decimal('comission', 19,8)->default(0);
            $table->decimal('executed_amount', 19,8)->default(0);
            $table->integer('leverage')->default(0);
            $table->tinyInteger('margin_mode')->default(1);
            $table->tinyInteger('trade_type')->default(1);
            $table->tinyInteger('is_position')->default(0);
            $table->dateTime('future_trade_time')->nullable();
            $table->dateTime('closed_time')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_market')->default(0);
            $table->tinyInteger('trigger_condition')->default(0);
            $table->decimal('current_market_price', 19,8)->default(0);
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
        Schema::dropIfExists('future_trade_long_shorts');
    }
}
