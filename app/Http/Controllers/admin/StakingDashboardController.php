<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Services\StakingDashboardService;

class StakingDashboardController extends Controller
{
    private $stakingDashboardService;

    public function __construct()
    {
        $this->stakingDashboardService = new StakingDashboardService;
    }
    public function dashboard()
    {
        $response = $this->stakingDashboardService->getDashboardData();
        
        return view('admin.staking.dashboard.index', $response['data']);
    }
}
