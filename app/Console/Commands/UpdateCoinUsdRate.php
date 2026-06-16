<?php

namespace App\Console\Commands;

use App\Http\Services\CurrencyService;
use Illuminate\Console\Command;

class UpdateCoinUsdRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-coin-usd-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update coin usd price';

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
        storeBotException("updateCoinUsdPrice:",'called');
        $currency = new CurrencyService();
        $response = $currency->updateCoinRateCorn();
        if(!$response["success"]) storeBotException("updateCoinUsdPrice:",$response["message"]);
        storeBotException("updateCoinUsdPrice:",$response["message"]);
    }
}
