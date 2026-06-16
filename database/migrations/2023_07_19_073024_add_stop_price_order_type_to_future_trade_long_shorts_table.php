<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStopPriceOrderTypeToFutureTradeLongShortsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('future_trade_long_shorts', function (Blueprint $table) {
            $table->tinyInteger('order_type')->default(1)->after('is_market');
            $table->decimal('stop_price',19,8)->default(0)->after('order_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('future_trade_long_shorts', function (Blueprint $table) {
            $table->dropColumn('order_type');
            $table->dropColumn('stop_price');
        });
    }
}
