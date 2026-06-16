<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTwoColumnToCoinPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->tinyInteger('enable_future_trade')->default(0)->after('bot_trading');
            $table->decimal('maintenance_margin_rate',19,8)->default(0)->after('enable_future_trade');
            $table->decimal('minimum_amount_future', 19, 8)->default(0)->after('maintenance_margin_rate');
            $table->decimal('leverage_fee')->default(0)->after('minimum_amount_future');
            $table->integer('max_leverage')->default(0)->after('leverage_fee');
            $table->tinyInteger('margin_type')->default(1)->after('max_leverage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->dropColumn('enable_future_trade');
            $table->dropColumn('maintenance_margin_rate');
            $table->dropColumn('leverage_fee');
            $table->dropColumn('max_leverage');
            $table->dropColumn('margin_type');
        });
    }
}
