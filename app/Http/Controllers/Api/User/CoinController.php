<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Services\CoinPairService;
use App\Http\Services\CoinService;
use App\Http\Services\Logger;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public $service;
    public $pairservice;
    public $logger;
    public function __construct()
    {
        $this->service = new CoinService();
        $this->pairservice = new CoinPairService();
        $this->logger = new Logger();
    }

    /**
     * all coin list
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoinList(){
        $coins = $this->service->getCoin(['status' => 1, 'trade_status' => 1]);
        return response()->json(['success'=>true,'data'=>$coins,'message'=>__('All Coins')]);
    }

    /**
     * all coin pair list
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCoinPairList(){
        $pairs = $this->pairservice->getAllCoinPairs();
        return response()->json($pairs);
    }
}
