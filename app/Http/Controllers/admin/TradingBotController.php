<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Services\TradingBotService;
use App\Model\Buy;
use App\User;
use Illuminate\Http\Request;

class TradingBotController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new TradingBotService();
    }

    public function botOrder() {

        $userData = User::where(['role' => USER_ROLE_ADMIN])->first();
        $user = User::where(['role' => USER_ROLE_ADMIN,'status' => STATUS_ACTIVE, 'is_default' => 1])->first();
        if ($user) {
            $userData = $user;
        }

        if(allsetting('enable_bot_trade') == STATUS_ACTIVE) {
            $response = $this->service->placeBotOrder($userData);
        }
        dd(11);
    }
}
