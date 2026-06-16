<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeReferralHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_referral_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trade_by')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('child_id')->nullable();
            $table->decimal('amount', 19, 8)->unsigned();
            $table->tinyInteger('percentage_amount')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->tinyInteger('level')->nullable();
            $table->string('coin_type')->nullable();
            $table->unsignedBigInteger('wallet_id')->nullable();
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
        Schema::dropIfExists('trade_referral_histories');
    }
}
