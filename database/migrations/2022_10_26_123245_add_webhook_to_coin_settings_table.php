<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebhookToCoinSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_settings', function (Blueprint $table) {
            $table->string("bitgo_webhook_label",180)->after("bitgo_wallet_id")->nullable();
            $table->string("bitgo_webhook_type",50)->after("bitgo_webhook_label")->nullable();
            $table->string("bitgo_webhook_url",180)->after("bitgo_webhook_type")->nullable();
            $table->string("bitgo_webhook_numConfirmations",50)->after("bitgo_webhook_url")->nullable();
            $table->string("bitgo_webhook_allToken",50)->after("bitgo_webhook_numConfirmations")->nullable();
            $table->string("bitgo_webhook_id",180)->after("bitgo_webhook_allToken")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_settings', function (Blueprint $table) {
            //
        });
    }
}
