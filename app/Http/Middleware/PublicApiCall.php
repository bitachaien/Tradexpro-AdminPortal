<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PublicApiCall
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
        $key = '{z)E/f+2sW?G!f]>E,rh^K4N-=8^Uw5dM]B9g<(mJ:HU^5?6~PwyewwM!a"}gs#N';

        if (!empty($request->header('publicapisecret')) && $request->header('publicapisecret') == $key) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('Invalid key')]);
        }
    }
}
