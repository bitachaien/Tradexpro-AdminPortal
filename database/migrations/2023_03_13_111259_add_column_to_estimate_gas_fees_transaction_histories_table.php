<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEstimateGasFeesTransactionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estimate_gas_fees_transaction_histories', function (Blueprint $table) {
            $table->tinyInteger('type')->after('deposit_id')->default(1);
        });
        Schema::table('admin_receive_token_transaction_histories', function (Blueprint $table) {
            $table->tinyInteger('type')->after('deposit_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estimate_gas_fees_transaction_histories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('admin_receive_token_transaction_histories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
