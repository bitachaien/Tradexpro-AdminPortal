<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('coin_id');
            $table->string('bitgo_wallet_id')->nullable();
            $table->tinyInteger('bitgo_deleted_status')->default(false);
            $table->tinyInteger('bitgo_approvalsRequired')->default(0);
            $table->string('bitgo_wallet_type')->nullable();
            $table->string('bitgo_wallet')->nullable();
            $table->integer('chain')->default(1);
            $table->tinyInteger('webhook_status')->default(0);
            $table->string('coin_api_user')->nullable();
            $table->string('coin_api_pass')->nullable();
            $table->string('coin_api_host')->nullable();
            $table->string('coin_api_port')->nullable();
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
        Schema::dropIfExists('coin_settings');
    }
}
