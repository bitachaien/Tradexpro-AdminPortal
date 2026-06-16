<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_card_banners', function (Blueprint $table) {
            $table->id();
            $table->string("uid");
            $table->string("title");
            $table->string("sub_title");
            $table->string("banner");
            $table->string("category_id");
            $table->unsignedInteger("updated_by");
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
        Schema::dropIfExists('gift_card_banners');
    }
}
