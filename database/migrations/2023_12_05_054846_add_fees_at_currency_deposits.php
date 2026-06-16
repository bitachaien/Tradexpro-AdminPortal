<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeesAtCurrencyDeposits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currency_deposits', function (Blueprint $table) {
            $table->tinyInteger('fees_type')->default(1);
            $table->decimal('fees',19,8)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currency_deposits', function (Blueprint $table) {
            //
        });
    }
}
