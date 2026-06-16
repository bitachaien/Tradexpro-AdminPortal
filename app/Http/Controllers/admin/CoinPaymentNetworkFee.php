<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\CoinPaymentNetworkFeeService;

class CoinPaymentNetworkFee extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new CoinPaymentNetworkFeeService();
    }

    public function list(Request $request)
    {
        if($request->ajax()) {
           $response = $this->service->getCoinPaymentNetworkFeeList();
            if($response['success']==true)
            {
                return datatables($response['data'])
                ->editColumn('coin_type', function ($item) {
                    return $item->coin_type;
                })
                ->editColumn('rate_btc', function ($item) {
                    return $item->rate_btc;
                })
                ->editColumn('tx_fee', function ($item) {
                    return $item->tx_fee;
                })
                ->editColumn('is_fiat', function ($item) {
                    return yesNo($item->is_fiat);
                })
                ->editColumn('status', function ($item) {
                    return yesNo($item->status,true);
                })
                ->editColumn('last_update', function ($item) {
                    return $item->created_at;
                })->make();
            }
        }
    }
    public function createOrUpdate(){
        $data = $this->service->CreateOrUpdate();
        if($data['success'])
            return redirect()->back()->with('success',$data['message']);
        return redirect()->back()->with('dismiss',$data['message']);
    }
}
