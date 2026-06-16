<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GenerateSecretKey
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
        $path = $request->path();
        $setting = settings(["generate_secret_key_enable"]);

        // check generate secret api access , return if access disabled
        $generateSecretKey = $setting["generate_secret_key_enable"] ?? false;
        if(! $generateSecretKey && $path == "api/generate-secret-key") {
            return response()->json(['success' => false, 'message' => __('Secret key generation is disabled')]);
        }

        return $next($request);
    }
}
