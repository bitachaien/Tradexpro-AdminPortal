<?php
namespace App\Http\Services;

class TradingBotOrderPlaceService
{
    public function placeBotBuySellOrder($orderData, $pair, $user)
    {
        try {
            $order_1 = $orderData['order_1'];
            $order_2 = $orderData['order_2'];
            $rand = getRandomInt(1,0);
            $rand = $rand % 2;
            if ($rand == 0) {
                $order_1 = $orderData['order_1'];
                $order_2 = $orderData['order_2'];
            } else {
                $order_1 = $orderData['order_2'];
                $order_2 = $orderData['order_1'];
            }
            if ($order_1['price'] && $order_1['price'] > 0 && $order_1['amount']) {
                // process operation
                $this->processBuyOrSellOrder($order_1, $pair, $user);
            }
            // sleep(1);
            if ($order_2['price'] && $order_2['price'] > 0 && $order_2['amount']) {
                // process operation
                $this->processBuyOrSellOrder($order_2, $pair, $user);
            }
        } catch(\Exception $e) {
            storeException('placeBotBuySellOrder', $e->getMessage());
        }
    }

    // place buy or sell order
    public function processBuyOrSellOrder($orderData, $pair, $user) {
        try {
            // storeBotException('processBuyOrSellOrder running', date('Y-m-d H:i:s'));

            if($orderData['orderType'] == TRADE_TYPE_BUY) {
                // storeBotException('processBuyOrSellOrder buy', 'start');
                $response = app(BuyOrderService::class)->createNewBotOrder($orderData,$pair,$user);
                // storeBotException('processBuyOrSellOrder sell', 'end');
            }
            if($orderData['orderType'] == TRADE_TYPE_SELL) {
                // storeBotException('processBuyOrSellOrder sell', 'start');
                $response = app(SellOrderService::class)->createNewBotOrder($orderData,$pair,$user);
                // storeBotException('processBuyOrSellOrder sell', 'end');
            }
        } catch(\Exception $e) {
            storeBotException('bot processBuyOrSellOrder', $e->getMessage());
        }
    }

    // create buy order for known pair
    public function createMarketBuyOrder($pair,$marketData,$user){
        if ($marketData) {
            $orderData['price'] = number_format($marketData->buy_price, 8, '.', '');
            $orderData['amount'] = $marketData->buy_amount;
            $response = app(BuyOrderService::class)->createNewBotOrder($orderData,$pair,$user);
        }
    }
    // create sell order for known pair
    public function createMarketSellOrder($pair,$marketData,$user){
        if ($marketData) {
            $orderData['price'] = number_format($marketData->sell_price, 8, '.', '');
            $orderData['amount'] = $marketData->sell_amount;
            $response = app(SellOrderService::class)->createNewBotOrder($orderData,$pair,$user);
        }
    }
}
