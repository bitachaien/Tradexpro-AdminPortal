<?php

namespace App\Http\Services\WebsocketServices;

use App\Contracts\WebsocketDataSenderInterface;
use App\Http\Services\DashboardService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderPlacedDataService implements WebsocketDataSenderInterface
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function sendData(Model $order)
    {
        $request = [];
        $request['base_coin_id'] = $order->base_coin_id;
        $request['trade_coin_id'] = $order->trade_coin_id;
        $request['price'] = $order->price;
        $request['amount'] = $order->amount;
        $request['dashboard_type'] = 'dashboard';
        $request['per_page'] = 50;
        $request['order_type'] = 'buy_sell';

        $request['userId'] = $order->user_id;
        $channelName = 'dashboard-'.$request['base_coin_id'].'-'.$request['trade_coin_id'];
        $eventName = 'order_place_'.$request['userId'];

        $socketData['open_orders'] = $this->dashboardService->getMyOrders((object)$request)['data'];
        $socketData['order_data'] = $this->dashboardService->getOrderDataTotal((object)$request)['data'];

        sendDataThroughWebSocket($channelName, $eventName, $socketData);

    }
}
