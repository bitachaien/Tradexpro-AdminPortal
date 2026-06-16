<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConditionBuysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('condition_buys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->integer('trade_coin_id');
            $table->integer('base_coin_id');
            $table->decimal('amount', 19, 8);
            $table->decimal('price',19, 8);
            $table->boolean('status')->default(false)->comment("false = pending, true = success");
            $table->decimal('btc_rate',19, 8);
            $table->tinyInteger('category')->default(1)->comment("1 = exchange");
            $table->decimal('maker_fees', 29, 18)->default(0);
            $table->decimal('taker_fees', 29, 18)->default(0);
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
        Schema::dropIfExists('condition_buys');
    }
}
