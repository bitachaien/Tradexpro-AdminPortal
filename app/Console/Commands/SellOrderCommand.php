<?php

namespace App\Console\Commands;

use App\Http\Services\MarketTradeService;
use App\Jobs\MarketBotSellOrderJob;
use App\Model\CoinPair;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SellOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sell:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'place sell order with coin pair';

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
        return 0;
        if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
//            storeBotException('SellOrderCommand active', date("Y-m-d H:i:s"));
            $admin = User::where(['role' => USER_ROLE_ADMIN,'status' => STATUS_ACTIVE])->orderBy('id', 'asc')->first();
            $requestData['user_id'] = $admin->id;
            $coinPairs = CoinPair::where(['status' => STATUS_ACTIVE, 'bot_trading' => STATUS_ACTIVE,'bot_trading_sell' => STATUS_ACTIVE])->get();
            if (isset($coinPairs[0])) {
                foreach ($coinPairs as $pair) {
                    $requestData['pair_id'] = $pair->id;
                    $requestData['bot_order_type'] = 'sell';
                    $request = new Request($requestData);
                    $service = new MarketTradeService();
                    $service->makeMarketOrder($request,$pair);
//                    dispatch(new MarketBotSellOrderJob($requestData,$pair))->onQueue('market-bot');
                }
            }
        } else {
            storeBotException('SellOrderCommand deactive', date("Y-m-d H:i:s"));
        }
    }

//    public function handle()
//    {
//        if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
////            storeException('SellOrderCommand active', date("Y-m-d H:i:s"));
//            $sellInterval = settings('trading_bot_sell_interval') ?? 20;
//            $sellInterval = intval($sellInterval);
//            $admin = User::where(['role' => USER_ROLE_ADMIN])->first();
//            $requestData['user_id'] = $admin->id;
//            if ($sellInterval >= 60) {
//                $interval = 1;
//            } else {
//                $interval = intval(60 / $sellInterval);
//            }
//            for ($i = 1; $i <= $interval; $i++) {
//                $coinPairs = CoinPair::where(['status' => STATUS_ACTIVE, 'bot_trading' => STATUS_ACTIVE,'bot_trading_sell' => STATUS_ACTIVE])->get();
//                if (isset($coinPairs[0])) {
//                    foreach ($coinPairs as $pair) {
//                        $requestData['pair_id'] = $pair->id;
//                        $requestData['bot_order_type'] = 'sell';
//                        dispatch(new MarketBotSellOrderJob($requestData))->onQueue('market-bot');
////                        $request = new Request($requestData);
////                        $service = new MarketTradeService();
////                        $service->makeMarketOrder($request);
//                    }
//                }
//                sleep($sellInterval);
//            }
//        } else {
//            storeException('SellOrderCommand deactive', date("Y-m-d H:i:s"));
//        }
//    }
}
