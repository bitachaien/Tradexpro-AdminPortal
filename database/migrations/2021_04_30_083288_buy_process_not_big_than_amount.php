<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BuyProcessNotBigThanAmount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE TRIGGER buy_process_not_big_than_amount
                              BEFORE UPDATE ON buys
                              FOR EACH ROW
                              BEGIN
                                declare msg varchar(128);
                                if new.processed > OLD.amount then
                                  set msg = concat(\'Process Not Bigger than Amount\');
                                  signal sqlstate \'45000\' set message_text = msg;
                                end if;
                              END;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS buy_process_not_big_than_amount');
    }
}
