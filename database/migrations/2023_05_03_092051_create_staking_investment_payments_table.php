<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStakingInvestmentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staking_investment_payments', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('staking_investment_id');
            $table->unsignedBigInteger('wallet_id');
            $table->string('coin_type');
            $table->tinyInteger('is_auto_renew')->default(0);
            $table->decimal('total_investment',19,8)->default(0);
            $table->decimal('total_bonus',19,8)->default(0);
            $table->decimal('total_amount',19,8)->default(0);
            $table->tinyInteger('investment_status')->default(5);
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
        Schema::dropIfExists('staking_investment_payments');
    }
}
