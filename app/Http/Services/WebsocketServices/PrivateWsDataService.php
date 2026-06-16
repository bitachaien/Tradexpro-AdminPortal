<?php

namespace App\Http\Services\WebsocketServices;

use App\Contracts\WebsocketDataSenderInterface;
use App\Http\Services\WebsocketServices\DataFetchers\AssetDataFetcherService;
use App\Http\Services\WebsocketServices\DataFetchers\UserOrderDataFetcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PrivateWsDataService implements WebsocketDataSenderInterface
{
    public function __construct(
        private AssetDataFetcherService $assetDataFetcherService,
        private UserOrderDataFetcher $userOrderDataFetcher,
    ) {}

    public function sendData($order) {
        if($order->user_id == get_super_admin_id()) {
            return;
        }
        $orderData = $this->userOrderDataFetcher->fetchData($order->base_coin_id, $order->trade_coin_id, $order->user_id);
        $assetData = $this->assetDataFetcherService->fetchData($order->base_coin_id, $order->trade_coin_id, $order->user_id);
        $channelName = 'dashboard-'.$order->base_coin_id.'-'.$order->trade_coin_id;
        $eventName = 'process-'.$order->user_id;
        sendDataThroughWebSocket(
            $channelName, 
            $eventName,
             array_merge($orderData, $assetData)
        );
        
        return array_merge($orderData, $assetData);
    }
}
