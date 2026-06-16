<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTvChart5minTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tv_chart_5mins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('interval')->unsigned();
            $table->string('base_coin_id', 11);
            $table->string('trade_coin_id', 11);
            $table->decimal('open',19,8);
            $table->decimal('close',19,8);
            $table->decimal('high',19,8);
            $table->decimal('low',19,8);
            $table->decimal('volume',19,8)->default(0);;
            $table->unique(['base_coin_id', 'trade_coin_id','interval']);
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
        Schema::dropIfExists('tv_chart_5mins');
    }
}
