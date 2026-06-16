<?php


namespace App\Http\Services;


use App\Model\Coin;
use App\Model\DepositeTransaction;

class PublicService
{

    public function __construct()
    {
    }

    // get order data
    public function getOrderdata($request)
    {
        $response['type'] = $request->order_type;
        $response['limit'] = $request->per_page;
        $service = new DashboardService();
        $data = $service->getOrders($request)['data'];
        if ($request->order_type == 'buy') {
            $buyData = $this->getBuyOrSellData($data['buy_orders']);
            $response['buys'] = $buyData;
        } elseif ($request->order_type == 'sell') {
            $sellData = $this->getBuyOrSellData($data['sell_orders']);
            $response['sells'] = $sellData;
        } else {
            $buyData = $this->getBuyOrSellData($data['buy_orders']);
            $sellData = $this->getBuyOrSellData($data['sell_orders']);
            $response['buys'] = $buyData;
            $response['sells'] = $sellData;
        }
        return $response;
    }

    // get buy/ sell data array
    public function getBuyOrSellData($items)
    {
        $data = [];
        if(isset($items[0])) {
            foreach ($items as $item) {
                $data[] = [
                    "date_time" => $item->created_at,
                    "exchanged" => $item->processed,
                    "price" => $item->price,
                    "amount" => $item->amount,
                    "total" => $item->total,
                ];
            }
        }
        return $data;
    }
}
