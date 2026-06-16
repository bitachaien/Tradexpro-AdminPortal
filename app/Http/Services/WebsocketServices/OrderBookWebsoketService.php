<?php

namespace App\Http\Services\WebsocketServices;

use App\Contracts\WebsocketDataSenderInterface;
use App\Http\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OrderBookWebsoketService implements WebsocketDataSenderInterface
{
    const EVENT_NAME = 'order_place';

    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function sendData($order)
    {
        $request = [];
        $request['base_coin_id'] = $order->base_coin_id;
        $request['trade_coin_id'] = $order->trade_coin_id;
        $request['price'] = $order->price;
        $request['amount'] = $order->amount;
        $request['dashboard_type'] = 'dashboard';
        $request['per_page'] = 50;
        $request['order_type'] = 'buy_sell';
        $socketData = $this->dashboardService->getAllOrderSocketData((object) $request);
        $channelName = 'dashboard-' . $order->base_coin_id . '-' . $order->trade_coin_id;

        sendDataThroughWebSocket($channelName, self::EVENT_NAME, $socketData);
    }
}
