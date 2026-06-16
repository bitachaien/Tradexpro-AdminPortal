<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $allowedIPs = [];
        if (!empty(env('ALLOWED_ADMIN_IPS'))) {
            $allowedIPs = explode(',', env('ALLOWED_ADMIN_IPS', ''));
        }

        $ipAllowed = empty($allowedIPs) || in_array($request->ip(), $allowedIPs);

        if ($ipAllowed) {
            if (!empty(Auth::user())) {
                if( !empty(Auth::user()->is_verified)) {
                    if(Auth::user()->status == STATUS_ACTIVE) {
                        if(Auth::user()->role == USER_ROLE_ADMIN) {
                            if(session()->has('g2f_checked'))  return $next($request);
                            $settings = settings();
                            $users = Auth::user();
                            $two_factor = json_decode($settings["two_factor_list"] ?? "{}",true);
                            if($two_factor && filter_var($settings["two_factor_admin"],FILTER_VALIDATE_BOOLEAN)){
                                if(in_array(GOOGLE_AUTH,$two_factor) && $users->g2f_enabled == ENABLE){
                                    return redirect()->route("twofactorCheck");
                                }else if(in_array(EMAIL_AUTH,$two_factor) && $users->email_enabled == ENABLE){
                                    return redirect()->route("twofactorCheck");
                                }else if(in_array(PHONE_AUTH,$two_factor) && $users->phone_enabled == ENABLE){
                                    return redirect()->route("twofactorCheck");
                                }
                                Auth::logout();
                                return redirect('login')->with('dismiss',__('You are not eligible for login in this panel'));
                            }else if($users->g2f_enabled == ENABLE) {
                                return redirect()->route("twofactorCheck");
                            }else if($users->email_enabled == ENABLE){
                                return redirect()->route("twofactorCheck");
                            }else if($users->phone_enabled == ENABLE){
                                return redirect()->route("twofactorCheck");
                            }else{
                                return $next($request);
                            }
                        } else {
                            Auth::logout();
                            return redirect('login')->with('dismiss',__('You are not eligible for login in this panel'));
                        }
                    } else {
                        Auth::logout();
                        return redirect('login')->with('dismiss',__('Your account is currently deactivate, Please contact to admin'));
                    }
                } else {
                    Auth::logout();
                    return redirect('login')->with('dismiss',__('Please verify your email'));
                }
            } else {
                Auth::logout();
                return redirect('login');
            }
        } else {
            Auth::logout();
            return redirect('login')->with('dismiss',__('This ip is not allowed to access admin panel'));
        }
    }
}
