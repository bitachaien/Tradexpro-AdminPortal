<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->string("gift_card_banner_id");
            $table->unsignedInteger("user_id");
            $table->string("coin_type");
            $table->unsignedInteger("wallet_type");
            $table->decimal("amount", 29, 18);
            $table->decimal("fees", 29, 18)->default(0);
            $table->string("redeem_code")->unique();
            $table->text("note")->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->tinyInteger('is_ads_created')->default(0);
            $table->tinyInteger("lock")->default(0);
            $table->tinyInteger("status")->default(1);
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
        Schema::dropIfExists('gift_cards');
    }
}
