<?php

namespace App\Console\Commands;

use App\Model\Buy;
use App\Model\Sell;
use App\Model\Transaction;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BotOrderRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botOrder:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bot success order removed ';

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
        //        storeException('BotOrderRemoveCommand time', date('Y-m-d H:i:s'));
        ini_set('max_execution_time', -1);
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $delOffset = 20;
            // delete old success bot buy order
            $buyIds = [];
            $buys = Buy::latest()->where(['is_bot' => STATUS_ACTIVE, 'status' => 1])
                ->take(10000)
                ->skip($delOffset)
                ->withTrashed()
                ->get();
            if (isset($buys[0])) {
                foreach ($buys as $buy) {
                    $buyIds[] = $buy->id;
                }
            }
            Buy::whereIn('id', $buyIds)->forcedelete();
            // delete old success bot sell order
            $sellIds = [];
            $sells = Sell::latest()->where(['is_bot' => STATUS_ACTIVE, 'status' => 1])
                ->take(10000)->skip($delOffset)->withTrashed()->get();
            if (isset($sells[0])) {
                foreach ($sells as $sell) {
                    $sellIds[] = $sell->id;
                }
            }
            Sell::whereIn('id', $sellIds)->forcedelete();

            // delete transaction which was executed by admin to admin
            $tranIds = [];
            $adminId = 1;
            $admin = User::where(['role' => USER_ROLE_ADMIN])->orderBy('id', 'asc')->first();
            if ($admin) {
                $adminId = $admin->id;
            }
            $transactions = Transaction::latest()->where(['buy_user_id' => $adminId, 'sell_user_id' => $adminId])
                ->take(10000)->skip($delOffset)->withTrashed()->get();
            if (isset($transactions[0])) {
                foreach ($transactions as $transaction) {
                    $tranIds[] = $transaction->id;
                }
            }
            Transaction::whereIn('id', $tranIds)->forcedelete();

            // delete active old order
            $lastOneHourBuy = Buy::where(['is_bot' => STATUS_ACTIVE, 'status' => 0])
                ->where('created_at', '>', \Carbon\Carbon::now()->subMinutes(45))
                ->withTrashed()
                ->count();
            if ($lastOneHourBuy == 0) {
                $lastOneHourBuy = 50;
            }
            $buyIdsActive = [];
            $buysActive = Buy::latest()->where(['is_bot' => STATUS_ACTIVE, 'status' => 0])
                ->take(10000)
                ->skip($lastOneHourBuy)
                ->withTrashed()
                ->get();
            if (isset($buysActive[0])) {
                foreach ($buysActive as $buy1) {
                    $buyIdsActive[] = $buy1->id;
                }
            }
            Buy::whereIn('id', $buyIdsActive)->forcedelete();

            $lastOneHourSell = Sell::where(['is_bot' => STATUS_ACTIVE, 'status' => 0])
                ->where('created_at', '>', \Carbon\Carbon::now()->subMinutes(45))
                ->withTrashed()
                ->count();
            if ($lastOneHourSell == 0) {
                $lastOneHourSell = 50;
            }
            $sellIdsActive = [];
            $sellsActive = Sell::latest()->where(['is_bot' => STATUS_ACTIVE, 'status' => 0])
                ->take(10000)
                ->skip($lastOneHourSell)
                ->withTrashed()
                ->get();

            if (isset($sellsActive[0])) {
                foreach ($sellsActive as $sell1) {
                    $sellIdsActive[] = $sell1->id;
                }
            }
            Sell::whereIn('id', $sellIdsActive)->forcedelete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            storeException('BotOrderRemoveCommand ex', $e->getMessage());
        }
    }
}
