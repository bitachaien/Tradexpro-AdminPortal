<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->bigInteger('coin_id');
            $table->string('key')->nullable();
            $table->tinyInteger('type')->default(PERSONAL_WALLET);
            $table->string('coin_type');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_primary')->default(0);
            $table->decimal('balance',29,18)->default(0);
            $table->unique(['user_id', 'coin_id']);
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
        Schema::dropIfExists('wallets');
    }
}
