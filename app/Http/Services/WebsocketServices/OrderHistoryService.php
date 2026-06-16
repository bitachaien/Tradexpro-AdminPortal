<?php

namespace App\Http\Services\WebsocketServices;

use App\Contracts\WebsocketDataSenderInterface;
use App\Http\Services\DashboardService;
use App\Http\Services\TransactionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderHistoryService implements WebsocketDataSenderInterface
{
    const MATCHED_TYPES = [
        'buy' => 'sell',
        'sell' => 'buy'
    ];

    public function __construct(
        private DashboardService $dashboardService,
        private TransactionService $transactionService
    ) {}

    public function sendData(Model $order)
    {
        $request = [];
        $request['base_coin_id'] = $order->base_coin_id;
        $request['trade_coin_id'] = $order->trade_coin_id;
        $request['dashboard_type'] = 'dashboard';
        $request['per_page'] = 50;
        $request['order_type'] = 'buy_sell';
        
        $request['user_id'] = $order->user_id;
        $request['userId'] = $order->user_id;
        $socketData = [];
        $socketData['open_orders'] = $this->dashboardService->getMyOrders((object)$request)['data'];
        $socketData['order_data'] = $this->dashboardService->getOrderDataTotal((object)$request)['data'];
        $socketData['my_trade']['transactions'] = $this->transactionService->getTradeHistoryForUsers(
            $order->base_coin_id, 
            $order->trade_coin_id, 
            $order->user_id, 
            $request['order_type'], 
             null
        );
        $channelName = 'dashboard-'.$order->base_coin_id.'-'.$order->trade_coin_id;
        $eventName = 'process-'.$order->user_id;
        
        sendDataThroughWebSocket(
            $channelName,
            $eventName,
            $socketData
        );
    }
}
