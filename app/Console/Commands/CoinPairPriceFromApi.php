<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Http\Services\TradingBotService;

class CoinPairPriceFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:price-from-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Get Coin Pair Price From API And Store In Store';

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
        storeBotException('CoinPairPriceFromApi running', date('Y-m-d H:i:s'));

        while(true) {
            $sleep = 0;
            $start = Carbon::now();
            if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
                $service = new TradingBotService();
                // Execute your command here
                storeBotException('CoinPairPriceFromApi  start', date('Y-m-d H:i:s'));
                $service->getCoinPairPriceFromApi();
                storeBotException('CoinPairPriceFromApi  end', date('Y-m-d H:i:s'));
                $end = Carbon::now();
                $differenceInSeconds = $end->diffInSeconds($start);
                storeBotException('CoinPairPriceFromApi differenceInSeconds', $differenceInSeconds);
                $intervalInSec = intval(settings('price_update_interval') ?? 30);
                $intervalInSec *= 60;
                $sleep = 0;
                if ($differenceInSeconds < $intervalInSec) {
                    $sleep = $intervalInSec - $differenceInSeconds;
                }
                storeBotException('CoinPairPriceFromApi sleep => ', $sleep);
                sleep($sleep);
            }
        }
    }
}
