<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDemoMiddleware
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
        if(env('APP_MODE') == 'demo')
        {
            if($request->ajax())
            {
                return response()->json(['success' => false, 'message' => __('Currently disable only for demo')]);
            }elseif ($request->header('accept') == "application/json") {
                return response()->json(['success' => false, 'message' => __('Currently disable only for demo')]);
            } else {
                return redirect()->back()->with(['dismiss' => __('Currently disable only for demo')]);
            }
        }
        return $next($request);
    }
}
