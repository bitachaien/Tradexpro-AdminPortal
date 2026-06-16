<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StakingInvestmentRequest;
use App\Http\Services\FaqService;
use Illuminate\Http\Request;
use App\Http\Services\StakingOfferService;

class StakingOfferController extends Controller
{
    private $stakingOfferService;
    private $faqService;

    public function __construct()
    {
        $this->stakingOfferService = new StakingOfferService;
        $this->faqService = new FaqService;
    }

    public function offerList(Request $request)
    {
        $response = $this->stakingOfferService->getOfferListBySearchGroupWise($request);
        return response()->json($response);
    }

    public function offerDetails(Request $request)
    {
        $response = $this->stakingOfferService->getOfferDetails($request->uid);
        return response()->json($response);
    }

    public function submitInvestment(StakingInvestmentRequest $request)
    {
        $response = $this->stakingOfferService->submitInvestment($request);
        return response()->json($response);
    }

    public function investmentList(Request $request)
    {
        $response = $this->stakingOfferService->getInvestmentListOfUserByPaginate($request);

        return response()->json($response);
    }

    public function investmentDetails(Request $request)
    {
        $response = $this->stakingOfferService->investmentDetails($request);

        return response()->json($response);
    }

    public function canceledInvestment(Request $request)
    {
        $response = $this->stakingOfferService->canceledInvestment($request);

        return response()->json($response);
    }

    public function earningList(Request $request)
    {
        $response = $this->stakingOfferService->earningListUserByPaginate($request);

        return response()->json($response);
    }

    public function investmentStatistics(Request $request)
    {
        $response = $this->stakingOfferService->earningStatisticsUser($request);

        return response()->json($response);
    }

    public function landingDetails(Request $request)
    {
        $response = $this->stakingOfferService->landingDetails();

        return response()->json($response);
    }

    public function investmentGetPaymentList(Request $request)
    {
        $response = $this->stakingOfferService->investmentGetPaymentList($request);

        return response()->json($response);
    }

    public function getTotalInvestmentBonus(Request $request)
    {
        $response = $this->stakingOfferService->getTotalInvestmentBonus($request);

        return response()->json($response);
    }
    
}
