<?php

namespace App\Observers;

use App\Model\FutureTradeLongShort;
use App\Http\Services\FutureTradeService;

class FutureTradeLongShortObserver
{
    /**
     * Handle the FutureTradeLongShort "created" event.
     *
     * @param  \App\Model\FutureTradeLongShort  $futureTradeLongShort
     * @return void
     */
    public function created(FutureTradeLongShort $futureTradeLongShort)
    {
        $futureTradeService = new FutureTradeService;
        $responseData = $futureTradeService->getFutureTradeSocketData($futureTradeLongShort->user_id,$futureTradeLongShort->base_coin_id, $futureTradeLongShort->trade_coin_id);
        $channel_name = 'future-trade-'.$futureTradeLongShort->user_id.'-'.$futureTradeLongShort->base_coin_id.'-'.$futureTradeLongShort->trade_coin_id;
        $event_name = 'future-trade-data';
        $socket_data = $responseData;

        sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
    }

    /**
     * Handle the FutureTradeLongShort "updated" event.
     *
     * @param  \App\Model\FutureTradeLongShort  $futureTradeLongShort
     * @return void
     */
    public function updated(FutureTradeLongShort $futureTradeLongShort)
    {
        $futureTradeService = new FutureTradeService;
        $responseData = $futureTradeService->getFutureTradeSocketData($futureTradeLongShort->user_id,$futureTradeLongShort->base_coin_id, $futureTradeLongShort->trade_coin_id);
        $channel_name = 'future-trade-'.$futureTradeLongShort->user_id.'-'.$futureTradeLongShort->base_coin_id.'-'.$futureTradeLongShort->trade_coin_id;
        $event_name = 'future-trade-data';
        $socket_data = $responseData;

        sendDataThroughWebSocket($channel_name,$event_name,$socket_data);
    }

    /**
     * Handle the FutureTradeLongShort "deleted" event.
     *
     * @param  \App\Model\FutureTradeLongShort  $futureTradeLongShort
     * @return void
     */
    public function deleted(FutureTradeLongShort $futureTradeLongShort)
    {
        //
    }

    /**
     * Handle the FutureTradeLongShort "restored" event.
     *
     * @param  \App\Model\FutureTradeLongShort  $futureTradeLongShort
     * @return void
     */
    public function restored(FutureTradeLongShort $futureTradeLongShort)
    {
        //
    }

    /**
     * Handle the FutureTradeLongShort "force deleted" event.
     *
     * @param  \App\Model\FutureTradeLongShort  $futureTradeLongShort
     * @return void
     */
    public function forceDeleted(FutureTradeLongShort $futureTradeLongShort)
    {
        //
    }
}
