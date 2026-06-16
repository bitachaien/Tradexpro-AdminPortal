<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinPaymentNetworkFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_payment_network_fees', function (Blueprint $table) {
            $table->id();
            $table->string('coin_type',80);
            $table->tinyInteger('is_fiat')->default(0);
            $table->string('last_update');
            $table->string('status');
            $table->decimal('tx_fee',19,8);
            $table->decimal('rate_btc',29,18);
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
        Schema::dropIfExists('coin_payment_network_fees');
    }
}
