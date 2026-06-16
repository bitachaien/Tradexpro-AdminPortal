<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('unique_code',180)->unique();
            $table->bigInteger('user_id');
            $table->bigInteger('wallet_id');
            $table->bigInteger('from_wallet_id')->nullable();
            $table->bigInteger('payment_method_id');
            $table->string('currency');
            $table->decimal('currency_amount',19,8);
            $table->decimal('coin_amount',19,8);
            $table->decimal('rate',19,8);
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('updated_by')->nullable();
            $table->string('bank_receipt')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('transaction_id')->nullable();
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
        Schema::dropIfExists('currency_deposits');
    }
}
