<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminApprovalAtCoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->tinyInteger('admin_approval')->default(1)->after('status');
        });
        Schema::table('withdraw_histories', function (Blueprint $table) {
            $table->tinyInteger('updated_by')->nullable()->after('status');
            $table->string('automatic_withdrawal')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coins', function (Blueprint $table) {
            //
        });
    }
}
