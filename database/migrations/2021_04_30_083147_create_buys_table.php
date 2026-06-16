<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('condition_buy_id')->unsigned()->nullable();
            $table->integer('trade_coin_id');
            $table->integer('base_coin_id');
            $table->decimal('amount', 19, 8)->unsigned();
            $table->decimal('price',19, 8)->unsigned();
            $table->decimal('processed', 19, 8)->default(0)->unsigned();
            $table->decimal('virtual_amount', 19, 8);
            $table->boolean('status')->default(false)->comment("false = pending, true = success");
            $table->decimal('btc_rate',19, 8);
            $table->boolean('is_market')->default(false)->comment("0 = normal, 2 = active");
            $table->boolean('is_conditioned')->default(false)->comment("0 = simple buy & 1 = condition buy");
            $table->tinyInteger('category')->default(1)->comment("1 = exchange");
            $table->decimal('maker_fees', 29, 18)->default(0);
            $table->decimal('taker_fees', 29, 18)->default(0);
            $table->decimal('request_amount',19,8)->unsigned()->default(0);
            $table->decimal('processed_request_amount',19,8)->unsigned()->default(0);
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
        Schema::dropIfExists('buys');
    }
}
