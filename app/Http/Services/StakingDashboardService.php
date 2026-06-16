<?php 
namespace App\Http\Services;
use App\Model\StakingInvestment;
use App\Model\StakingInvestmentPayment;

class StakingDashboardService {

    public function getDashboardData()
    {
        $data['total_investment'] = StakingInvestment::where('status','<>', STAKING_INVESTMENT_STATUS_CANCELED)->sum('investment_amount');
        $data['total_investment_bonus'] = StakingInvestment::where('status','<>', STAKING_INVESTMENT_STATUS_CANCELED)->sum('total_bonus');
        $data['total_return_investment'] = StakingInvestmentPayment::sum('total_investment');
        $data['total_given_bonus'] = StakingInvestmentPayment::sum('total_bonus');
    
        $response = ['success'=>true, 'message'=>__('Dashboard Data'), 'data'=>$data];

        return $response;
    }
}