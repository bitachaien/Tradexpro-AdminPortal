<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Model\UserApiWhiteList;
use Illuminate\Support\Facades\RateLimiter;

class PrivateApiIpRateCheck
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
        
        $setting = defined("ADMIN_SETTINGS") ? ADMIN_SETTINGS : settings();

        if(!$user = $request->user()){
            return response()->json(
                [
                    'success' => false, 
                    'message' => __('Authenticate user not found')
                ]
            );
        }

        $ip = $request->ip();
        $whiteListEnable = $setting["api_secret_whitelist_enable"] ?? false;
        if($whiteListEnable){

            $whiteList = UserApiWhiteList::query()
                        ->whereUserId($user->id)
                        ->whereIpAddress($ip)
                        ->whereStatus(STATUS_ACTIVE)
                        ->first();
                        
            if(!$whiteList){
                return response()->json(
                    [
                        'success' => false, 
                        'message' => __('Unauthorize ip')
                    ]
                );
            }

            $requestLimit = $setting["rate_limit_ip_per_minute"] ?? 1000;
            $ipLimiter = RateLimiter::attempt(
                'private-api-access:'.$ip,
                $perMinute = $requestLimit,
                function(){}
            );
            
            if (! $ipLimiter) {
                return response()->json(
                    [
                        'success' => false, 
                        'message' => __('Per minute requests exceed')
                    ]
                );
            }

            $privateApiCheck = new PrivateApiCheck;

            // check trade api access , return if access disabled
            $api_access_trade = $whiteList->trade_access ?? false;
            if(!$privateApiCheck->checkTradeApi($api_access_trade))
                return response()->json(['success' => false, 'message' => __('This trade api access is disabled')]);
    
            // check withdrawal api access , return if access disabled
            $api_access_withdrawal = $whiteList->withdrawal_access ?? false;
            if(!$privateApiCheck->checkWithdrawalApi($api_access_withdrawal))
                return response()->json(['success' => false, 'message' => __('This withdrawal api access is disabled')]);

            $whiteList->increment("number_of_request");
        }

        return $next($request);
    }
}
