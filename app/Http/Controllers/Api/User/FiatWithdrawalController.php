<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\Api\User\FiatWithdrawalRateRequest;
use App\Model\CurrencyDepositPaymentMethod;
use App\Model\UserBank;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\FiatWithdrawalService;
use App\Http\Requests\Api\User\FiatWithdrawalRequest;
use App\Http\Requests\Api\User\FiatWalletWithdrawalRequest;

class FiatWithdrawalController extends Controller
{
    private $service;
    function __construct()
    {
        $this->service = new FiatWithdrawalService();
    }
    // fiat withdrawal data
    public function fiatWithdrawal()
    {
        $response = $this->service->getFiatWithdrawalData(Auth::id());

        return response()->json($response);
    }

    // fiat withdrawal rate
    public function getFiatWithdrawalRate(FiatWithdrawalRateRequest $request)
    {
        $response = $this->service->getFiatWithdrawalRateData($request, Auth::id());

        return response()->json($response);
    }
    // fiat withdrawal process
    public function fiatWithdrawalProcess(FiatWithdrawalRequest $request)
    {
        $response = $this->service->fiatWithdrawalProcess($request, Auth::id());

        return response()->json($response);
    }
    // fiat withdrawal list
    public function fiatWithdrawHistory(Request $request)
    {
        return $this->handlerApiResponse(function () use ($request): array {
            return $this->service->getWithdrawalHistory(auth()->id(), $request->per_page, $request->search);
        });
    }

    public function getWalletCurrencyWithdrawalPage(Request $request)
    {
        $data['my_bank'] = UserBank::where(['user_id' => getUserId(), 'status' => STATUS_ACTIVE])->get();
        $data['payment_method_list'] = CurrencyDepositPaymentMethod::where('type', 'fiat-withdrawl')->where('status', STATUS_ACTIVE)->get();

        return response()->json(responseData(true, __("Withdrawal data get successfully"), $data));
    }

    public function fiatWalletWithdrawalProcess(FiatWalletWithdrawalRequest $request)
    {
        return response()->json(
            $this->service->fiatWalletWithdrawalProcess($request, Auth::id())
        );
    }

    public function fiatWalletWithdrawalHistory(Request $request)
    {
        return response()->json(
            $this->service->fiatWalletWithdrawalHistory($request)
        );
    }
}
