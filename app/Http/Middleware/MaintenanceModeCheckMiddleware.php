<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
class MaintenanceModeCheckMiddleware
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
        $maintenanceModeStatus = allsetting('maintenance_mode_status') ?? STATUS_PENDING;

        if ($maintenanceModeStatus == STATUS_PENDING) {
            return $next($request);
        } else {
            $maintenanceModeTitle = allsetting('maintenance_mode_title') ?? 'Maintenance Mode On';
            $maintenanceModeText = allsetting('maintenance_mode_text') ?? '';
            $maintenanceModeImg = allsetting('maintenance_mode_img') ?? '';
            $data = [
                'maintenance_mode_status'=>$maintenanceModeStatus,
                'maintenance_mode_title'=>$maintenanceModeTitle,
                'maintenance_mode_text'=>$maintenanceModeText,
                'maintenance_mode_img'=>!empty($maintenanceModeImg) ? asset(path_image().$maintenanceModeImg) : ''
            ];
            return response()->json(['success' => false, 'maintenance' => STATUS_ACTIVE, 'message' => $maintenanceModeTitle,'data'=>$data]);
        }

    }
}
