<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckApi
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
        $lang = $request->header('lang') ?? 'en';
        try {
            app()->setLocale($lang);
        } catch (\Exception $e) {
            storeException('error', "lang key got: $lang");
            storeException('error', processExceptionMsg($e));
            app()->setLocale('en');
        }

        $allowedOrigins = explode(',', env('FRONTEND_URL', '')); // Updated to allow empty array if env is empty
        $origin = $request->header('Origin');

        $apiKey = env('USER_API_SECRET_KEY', 'h0vWu6MkInNlWHJVfIXmHbIbC66cQvlbSUQI09Whbp');

        $allowedIPs = explode(',', env('ALLOWED_IPS', ''));

        // Conditions check
        $originAllowed = (empty($allowedOrigins) && $origin === null) || in_array($origin, $allowedOrigins);
        $headerKeyMatches = ($request->header('userapisecret') && $request->header('userapisecret') === $apiKey) ||
                            ($request->header('userpublickey') && $request->header('userpublickey') === $apiKey);
        $ipAllowed = empty($allowedIPs) || in_array($request->ip(), $allowedIPs);

        // Final check
        if ($originAllowed || $headerKeyMatches || $ipAllowed) {
            return $next($request);
        }

        return response()->json(['error' => 'Unaccessable', 'success' => false, 'message' => __('Access denied')], 403);

    }
}
