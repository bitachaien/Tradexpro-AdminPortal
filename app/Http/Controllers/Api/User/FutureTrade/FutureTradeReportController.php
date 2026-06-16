<?php

namespace App\Http\Controllers\Api\User\FutureTrade;

use App\Http\Controllers\Controller;
use App\Http\Services\FutureTradeService;
use Illuminate\Http\Request;

class FutureTradeReportController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new FutureTradeService();
    }
    // get tp sl details
    public function getTpSlDetails($uid) {
        $response = responseData(false,'Something went wrong');
        try {
            $response = $this->service->getTpSlDetailsData($uid, auth()->user());
        } catch(\Exception $e) {
            storeException("getTpSlDetails", $e->getMessage());
        }
        return $response;
    }
}
