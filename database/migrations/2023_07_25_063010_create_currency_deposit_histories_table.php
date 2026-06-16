<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyDepositHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_deposit_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('payment_id');
            $table->unsignedTinyInteger('payment_type');
            $table->unsignedInteger('wallet_id');
            $table->unsignedInteger('coin_id');
            $table->string('coin_type',15);
            $table->unsignedInteger('bank_id')->nullable();
            $table->string('bank_recipt')->nullable();
            $table->decimal('amount', 29,2)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->string('transaction_id')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('currency_deposit_histories');
    }
}
