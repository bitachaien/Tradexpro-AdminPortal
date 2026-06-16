<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStakingInvestmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staking_investments', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('staking_offer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('coin_type');
            $table->unsignedInteger('period');
            $table->decimal('offer_percentage',19,8);
            $table->tinyInteger('terms_type');
            $table->integer('minimum_maturity_period');
            $table->tinyInteger('auto_renew_status')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->decimal('investment_amount',19,8);
            $table->decimal('earn_daily_bonus',19,8);
            $table->decimal('total_bonus',19,8);
            $table->unsignedBigInteger('auto_renew_from')->nullable();
            $table->tinyInteger('is_auto_renew')->default(0);
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
        Schema::dropIfExists('staking_investments');
    }
}
