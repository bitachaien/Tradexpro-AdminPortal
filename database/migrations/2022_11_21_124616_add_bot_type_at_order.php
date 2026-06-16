<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotTypeAtOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->tinyInteger('is_token')->default(0)->after('status');
        });
        Schema::table('buys', function (Blueprint $table) {
            $table->tinyInteger('is_bot')->default(0)->after('status');
        });
        Schema::table('sells', function (Blueprint $table) {
            $table->tinyInteger('is_bot')->default(0)->after('status');
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
            //
        });
        Schema::table('buys', function (Blueprint $table) {
            //
        });
        Schema::table('sells', function (Blueprint $table) {
            //
        });
    }
}
