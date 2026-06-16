<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('coin_pair_id')->constrained('coin_pairs')->onDelete('cascade');
            $table->decimal('maker_fee', 11, 8);
            $table->decimal('taker_fee', 11, 8);
            $table->integer('status')->default(STATUS_ACTIVE);
            $table->unique(['user_id', 'coin_pair_id']);
            
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
        Schema::dropIfExists('trade_fees');
    }
}
