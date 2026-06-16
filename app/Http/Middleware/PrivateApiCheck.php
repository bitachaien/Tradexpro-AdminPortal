<?php

namespace App\Http\Middleware;

use App\Model\UserSecretKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Http\Controllers\Api\User\ReportController;
use App\Http\Controllers\Api\User\WalletController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\User\BuyOrderController;
use App\Http\Controllers\Api\User\ExchangeController;
use App\Http\Controllers\Api\User\SellOrderController;

class PrivateApiCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // return next if called form frontend
        $frontedCall = defined("IS_PUBLIC_API");
        if(! $frontedCall) return $next($request);

        $setting = settings();
        define("ADMIN_SETTINGS", $setting);

        // check api access , return if access disabled
        $api_access = $setting["api_access_enable"] ?? false;
        if(! $api_access) {
            return response()->json(['success' => false, 'message' => __('Public api access is disabled')]);
        }

        $user = auth()->user() ?? auth()->guard('api')->user();
        $secretKey = USER_SECRET_DATA; //UserSecretKey::where("user_id", $user->id)->first();

        if(! $user->api_access_allow_user) {
            return response()->json(['success' => false, 'message' => __('Api access is blocked')]);
        }
        
        if(! $secretKey->status) {
            return response()->json(['success' => false, 'message' => __('Api access is blocked')]);
        }
        
        // check trade api access , return if access disabled
        $api_access_trade = $setting["api_access_trade_enable"] ?? false;
        if(!$this->checkTradeApi($api_access_trade, $user->trade_access_allow_user, $secretKey->trade_access))
            return response()->json(['success' => false, 'message' => __('This trade api access is disabled')]);
 
        // check withdrawal api access , return if access disabled
        $api_access_withdrawal = $setting["api_access_withdraw_enable"] ?? false;
        if(!$this->checkWithdrawalApi($api_access_withdrawal, $user->withdrawal_access_allow_user,$secretKey->withdrawal_access))
            return response()->json(['success' => false, 'message' => __('This withdrawal api access is disabled')]);
        
        return $next($request);
    }

    public function getActionMethod()
    {
        // get target controller
        return explode('@', app("router")->currentRouteAction());
    }

    public function checkTradeApi($api_access_trade, $user_blocked_trade = true, $user_turned_off_trade = true)
    {   
        
        $routerActionController = $this->getActionMethod();
        // check trade api access , return if access disabled
        if(
            isset($routerActionController[0]) &&
            isset($routerActionController[1]) &&
            (
                // ignore controller method matches controller method
                $routerActionController[1] !== "getReferralHistory"
            )&&(
                // check controller matches controller
                in_array($routerActionController[0], [
                    BuyOrderController::class, 
                    SellOrderController::class, 
                    ExchangeController::class, 
                    ReportController::class
                ])
            )
        )   {
            if(!$api_access_trade) return false;
            if(!$user_blocked_trade) return false;
            if(!$user_turned_off_trade) return false;
        }

        return true;
    }

    public function checkWithdrawalApi($api_access_withdrawal, $user_blocked_withdrawal = true, $user_turned_off_withdrawal = true)
    {
        $routerActionController = $this->getActionMethod();

        if(
            isset($routerActionController[0]) &&
            isset($routerActionController[1]) &&
            (
                // ignore controller method matches controller method
                $routerActionController[1] !== "walletList"     &&
                $routerActionController[1] !== "walletDeposit"
            )&&(
                // check controller matches controller
                $routerActionController[0] == WalletController::class
            )
        )   {
            if(!$api_access_withdrawal) return false;
            if(!$user_blocked_withdrawal) return false;
            if(!$user_turned_off_withdrawal) return false;
        }

        return true;
    }
}
