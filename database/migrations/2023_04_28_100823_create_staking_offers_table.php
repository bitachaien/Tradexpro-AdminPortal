<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStakingOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staking_offers', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('created_by');
            $table->string('coin_type');
            $table->unsignedInteger('period');
            $table->decimal('offer_percentage',19,8)->default(0);
            $table->decimal('minimum_investment',19,8)->default(0);
            $table->decimal('maximum_investment',19,8)->default(0);
            $table->tinyInteger('terms_type')->default(1);
            $table->integer('minimum_maturity_period')->default(0);
            $table->longText('terms_condition')->nullable();
            $table->integer('registration_before')->default(0);
            $table->tinyInteger('phone_verification')->default(0);
            $table->tinyInteger('kyc_verification')->default(0);
            $table->decimal('user_minimum_holding_amount',19,8)->default(0);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('staking_offers');
    }
}
