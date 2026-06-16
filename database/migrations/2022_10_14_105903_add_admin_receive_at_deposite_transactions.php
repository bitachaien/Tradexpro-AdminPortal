<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminReceiveAtDepositeTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deposite_transactions', function (Blueprint $table) {
            $table->tinyInteger('is_admin_receive')->default(0)->after('status');
            $table->decimal('received_amount',29,18)->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposite_transactions', function (Blueprint $table) {
            //
        });
    }
}
