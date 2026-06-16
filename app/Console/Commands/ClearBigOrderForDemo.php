<?php

namespace App\Console\Commands;

use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearBigOrderForDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear-big-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command clear big order from demo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        storeBotException('ClearBigOrderForDemo','start');
        if (env('APP_MODE') == 'demo') {
            $buys = DB::table('buys')->where(['base_coin_id' => 2,'trade_coin_id' => 1, 'user_id' => 2,'status'=> 0])
                ->where('amount','>=', 4)
                ->delete();
            $sells = DB::table('sells')->where(['base_coin_id' => 2,'trade_coin_id' => 1, 'user_id' => 2,'status'=> 0])
                ->where('amount','>=', 4)
                ->delete();
        }
        storeBotException('ClearBigOrderForDemo','end');
    }
}
