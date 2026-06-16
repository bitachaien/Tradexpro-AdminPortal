<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_limits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('condition_buy_id')->unsigned()->nullable();
            $table->integer('trade_coin_id');
            $table->integer('base_coin_id');
            $table->decimal('stop',19, 8);
            $table->decimal('limit_price',19, 8);
            $table->decimal('amount', 19, 8);
            $table->string('order',11);
            $table->tinyInteger('is_conditioned')->default(0)->comment('0 = simple stop limits, 1 = advanced stop limit');
            $table->tinyInteger('category')->default(1)->comment('1 = exchange');
            $table->decimal('maker_fees', 29, 18)->default(0);
            $table->decimal('taker_fees', 29, 18)->default(0);
            $table->tinyInteger('status')->default(0);
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
        Schema::dropIfExists('stop_limits');
    }
}
