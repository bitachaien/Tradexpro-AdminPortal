<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StakingCheck
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
        if(settings('enable_staking') ?? 0)
            return $next($request);
        
        return response()->json([
            'success' => false,
            'message' => __("Staking feature not enabled")
        ]);
    }
}
