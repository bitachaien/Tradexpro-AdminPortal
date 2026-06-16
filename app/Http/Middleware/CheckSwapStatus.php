<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSwapStatus
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
        if(allsetting('swap_status') == 1) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('Swap feature is disable right now.')]);
        }
    }
}
