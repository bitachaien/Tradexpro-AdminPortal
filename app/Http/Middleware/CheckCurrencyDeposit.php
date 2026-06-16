<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCurrencyDeposit
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
        if(allsetting('currency_deposit_status') == STATUS_ACTIVE) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('Currency deposit feature is disable right now.')]);
        }
    }
}
