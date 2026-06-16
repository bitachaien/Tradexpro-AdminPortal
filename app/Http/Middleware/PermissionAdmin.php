<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\Permission;
use App\Model\PermissionFromData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PermissionAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module=null)
    {
        $user = Auth::user();
        if($user->role == USER_ROLE_ADMIN && $user->super_admin) {
            define("SUPER_ADMIN", true);
            return $next($request);
        }

        $route = Route::current()->action;
        if(!isset($route['group'])) return redirect()->route('adminDashboard')->with('dismiss',__('You do not have permission!'));
        if(in_array($route['group'],allowedGroup())) return $next($request);

        $permission = PermissionFromData::whereGroup($route['group'])->get();
        if(count($permission) == 0) return redirect()->route('adminDashboard')->with('dismiss',__('You do not have permission!'));
        if($route['group'] == 'Addon' && !empty($module)) {
            $as = $module;
        } else {
            $as = str_replace('.','_',$route['as']);
        }

        $got_you = false;
        foreach ($permission as $per){
            if($per->route == $as){
                $permission_data = Permission::where(['role_id' => $user->role_id,'route' => $as,'group' => $route['group']])->first();
                if ($permission_data) {
                    return $next($request);
                }
                $got_you = true;
            }
        }
        if($got_you) return redirect()->back()->with('dismiss',__('You do not have permission!'));
        $permission_data = Permission::where(['role_id' => $user->role_id,'group' => $route['group']])->first();
        if($permission_data) return $next($request);

        return redirect()->back()->with('dismiss',__('You do not have permission!'));
    }
}
