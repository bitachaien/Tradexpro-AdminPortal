<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardRedeemHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_card_redeem_histories', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->unsignedInteger("gift_card_id");
            $table->unsignedInteger("receiver_id");
            $table->string("coin_type");
            $table->decimal("amount", 29, 18)->default(0);
            $table->tinyInteger("status")->default(1);
            $table->unsignedInteger("updated_by");
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
        Schema::dropIfExists('gift_card_redeem_histories');
    }
}
