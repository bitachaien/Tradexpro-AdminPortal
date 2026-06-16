<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckIcoFeature
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
        if(DB::connection()->getDatabaseName()) {
            if (Schema::hasTable('admin_settings')) {
                if (allsetting('launchpad_settings') == 1) {
                    return $next($request);
                } else {
                    if ($request->header('accept') == "application/json") {
                        return response()->json(['success' => false, 'disable' => true, 'message' => __('Ico feature is disable')]);
                    }
                    return redirect()->route('adminDashboard')->with('dismiss',__('Ico feature is disable'));
                }
            }
        }

        return $next($request);
    }
}
