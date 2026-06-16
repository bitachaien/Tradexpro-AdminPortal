<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTransactionLastPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE TRIGGER update_transaction_last_price
            BEFORE INSERT
            ON transactions FOR EACH ROW
            BEGIN
              SET NEW.last_price = (select price from transactions 
              where 
                base_coin_id = NEW.base_coin_id 
                  and 
                trade_coin_id = NEW.trade_coin_id
                order by created_at desc limit 1 );
            END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_transaction_last_price');
    }
}
