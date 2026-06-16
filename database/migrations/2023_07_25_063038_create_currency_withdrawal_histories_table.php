<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyWithdrawalHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_withdrawal_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('coin_id');
            $table->unsignedInteger('bank_id');
            $table->string('coin_type',20);
            $table->decimal('amount', 29, 2)->default(0);
            $table->decimal('fees', 29, 8)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->string('receipt')->nullable();
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
        Schema::dropIfExists('currency_withdrawal_histories');
    }
}
