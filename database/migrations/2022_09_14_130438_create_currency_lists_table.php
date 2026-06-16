<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code',180)->unique();
            $table->string('symbol',50);
            $table->decimal('rate',19,2)->default(1);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_primary')->default(0);
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
        Schema::dropIfExists('currency_lists');
    }
}
