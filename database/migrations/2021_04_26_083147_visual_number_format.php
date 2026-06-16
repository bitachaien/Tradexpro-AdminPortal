<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VisualNumberFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("CREATE FUNCTION visualNumberFormat (number DECIMAL(19,8)) RETURNS varchar(20) DETERMINISTIC BEGIN IF INSTR(trim(number)+0, '.') = 0 THEN RETURN concat(trim(number)+0,'.00');  ELSE  RETURN trim(number)+0;  END IF; END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP function IF EXISTS visualNumberFormat;');
    }
}
