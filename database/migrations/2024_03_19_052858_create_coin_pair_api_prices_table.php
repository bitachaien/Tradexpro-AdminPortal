<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinPairApiPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_pair_api_prices', function (Blueprint $table) {
            $table->id();
            $table->string('pair', 20);
            $table->string('buy_price')->default("0");
            $table->string('buy_amount')->default("0");
            $table->string('sell_price')->default("0");
            $table->string('sell_amount')->default("0");
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
        Schema::dropIfExists('coin_pair_api_prices');
    }
}
