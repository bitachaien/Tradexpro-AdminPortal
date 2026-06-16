<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('coin_type',20)->unique();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('network')->default(1);
            $table->tinyInteger('is_withdrawal')->default(1);
            $table->tinyInteger('is_deposit')->default(1);
            $table->tinyInteger('is_buy')->default(1);
            $table->tinyInteger('is_sell')->default(1);
            $table->string('coin_icon', 50)->nullable();
            $table->boolean('is_base')->default(1);
            $table->boolean('is_currency')->default(0);
            $table->boolean('is_primary')->nullable()->unique();
            $table->boolean('is_wallet')->default(0);
            $table->boolean('is_transferable')->default(0);
            $table->boolean('is_virtual_amount')->default(0);
            $table->tinyInteger('trade_status')->default(1);
            $table->string('sign')->nullable()->collation('utf8_unicode_ci');
            $table->decimal('minimum_buy_amount', 19.0, 8.0)->default(0.0000001);
            $table->decimal('maximum_buy_amount', 19.0, 8.0)->default(999999);
            $table->decimal('minimum_sell_amount', 19.0, 8.0)->default(0.0000001);
            $table->decimal('maximum_sell_amount', 19.0, 8.0)->default(999999);
            $table->decimal('minimum_withdrawal', 19, 8)->default(0.0000001);
            $table->decimal('maximum_withdrawal', 19, 8)->default(99999999.0);
            $table->decimal('max_send_limit', 19, 8)->default(0.0000001);
            $table->decimal('withdrawal_fees', 29, 18)->default(	0.0000001);
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
        Schema::dropIfExists('coins');
    }
}
