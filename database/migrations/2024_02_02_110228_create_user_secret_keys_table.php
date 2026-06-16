<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSecretKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_secret_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('secret_key')->unique();
            $table->date('start_date');
            $table->date('expire_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('number_of_request')->default(0);
            $table->unsignedBigInteger('target_request')->default(0);
            $table->tinyInteger('trade_access')->default(1);
            $table->tinyInteger('withdrawal_access')->default(1);
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
        Schema::dropIfExists('user_secret_keys');
    }
}
