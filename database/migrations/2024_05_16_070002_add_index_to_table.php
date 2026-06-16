<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->index('parent_coin_id');
            $table->index('child_coin_id');
        });

        Schema::table('buys', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('sells', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_1days', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_2hours', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_4hours', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_5mins', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_15mins', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
        });

        Schema::table('tv_chart_30mins', function (Blueprint $table) {
            $table->index('trade_coin_id');
            $table->index('base_coin_id');
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
            $table->dropIndex(['parent_coin_id']);
            $table->dropIndex(['child_coin_id']);
        });

        Schema::table('buys', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('sells', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_1days', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_2hours', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_4hours', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_5mins', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_15mins', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });

        Schema::table('tv_chart_30mins', function (Blueprint $table) {
            $table->dropIndex(['trade_coin_id']);
            $table->dropIndex(['base_coin_id']);
        });
    }
}
