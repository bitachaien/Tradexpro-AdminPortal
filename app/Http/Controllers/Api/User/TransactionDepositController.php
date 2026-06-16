<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\TransactionDepositService;
use App\Http\Requests\Api\User\TransactionDepositRequest;

class TransactionDepositController extends Controller
{
    protected $service;
    public function __construct(TransactionDepositService $service) {
        $this->service = $service;
    }
    public function getNetwork()
    {
        return response()->json(
            $this->service->getNetworks()
        );
    }
    
    public function getCoinNetwork(Request $request)
    {
        return response()->json(
            $this->service->getCoinNetwork($request)
        );
    }
    
    public function checkCoinTransaction(TransactionDepositRequest $request)
    {
        return $this->handlerApiResponse(function () use ($request) {
            return $this->service->checkCoinTransactionAndDeposit($request);
        });
    }
}
