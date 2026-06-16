<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GiftCardCheck
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
        if(settings('enable_gift_card') ?? 0)
        return $next($request);
        return redirect()->back()->with('dismiss', __("Gift card feature not enabled"));
    }
}
