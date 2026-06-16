<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotOptionAtCoinPair extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_pairs', function (Blueprint $table) {
            $table->enum('bot_operation',['neutral','increase','decrease','random'])->default('random');
            $table->decimal('bot_percentage',19,8)->default(0);
            $table->decimal('upper_threshold',29,18)->default(0);
            $table->decimal('lower_threshold',29,18)->default(0);
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
    }
}
