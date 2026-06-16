<?php

namespace App\Jobs;

use App\Http\Services\MarketTradeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarketBotSellOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $data ;
    private $pair ;
    public function __construct($requestData,$pair)
    {
        $this->data = $requestData;
        $this->pair = $pair;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        storeException('MarketBotSellOrderJob',date('Y-m-d H:i:s'));
        $request = new Request($this->data);
        $service = new MarketTradeService();
        $service->makeMarketOrder($request,$this->pair);
    }
}
