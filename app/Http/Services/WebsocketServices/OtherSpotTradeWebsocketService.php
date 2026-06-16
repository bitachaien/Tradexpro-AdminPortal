<?php

namespace App\Http\Services\WebsocketServices;

use App\Contracts\WebsocketDataSenderInterface;
use App\Http\Services\CoinPairService;
use App\Http\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OtherSpotTradeWebsocketService implements WebsocketDataSenderInterface
{
    const OPPOSITE_ORDER_TYPE = [
        'buy' => 'sell',
        'sell' => 'buy'
    ];

    public function __construct(
        private DashboardService $dashboardService,
        private CoinPairService $coinPairService,

    ) {}

    public function sendData(Model $order) 
    {
        $request = [];
        $request['base_coin_id'] = $order->base_coin_id;
        $request['trade_coin_id'] = $order->trade_coin_id;
        $request['dashboard_type'] = 'dashboard';
        $request['per_page'] = 50;
        $request['order_type'] = self::OPPOSITE_ORDER_TYPE[
            strtolower(Str::singular($order->getTable()))
        ];

        $socketData['pairs'] = $this->coinPairService->getAllCoinPairs()['data'];
        $socketData['trades'] = $this->dashboardService->getMarketTransactions((object) $request)['data'];
        $socketData['last_trade'] = $this->dashboardService->getMarketLastTransactions((object) $request)['data'];
        if($socketData['last_trade']) 
        {
            $socketData['last_trade']['last_trade_time'] = strtotime($socketData['last_trade']['time']);
            $socketData['last_trade']['time'] = Carbon::parse($socketData['last_trade']['time'])->timestamp;
        }

        $socketData['last_price_data'] = $this->dashboardService->getDashboardMarketTradeDataTwo($order->base_coin_id, $order->trade_coin_id,2);
        $socketData['order_data'] = $this->dashboardService->getOrderDataWhenProcess((object) $request)['data'];
        $request['order_type'] = 'buy_sell';
        $socketData['orders'] = $this->dashboardService->getOrders((object) $request)['data'];

        $channelName = 'trade-info-'.$order->base_coin_id.'-'.$order->trade_coin_id;
        $eventName = 'process';
        $socketData['update_trade_history'] = false;
        sendDataThroughWebSocket($channelName,$eventName,$socketData);
    }
}
