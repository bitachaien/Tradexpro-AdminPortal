<?php

namespace App\Http\Middleware;

use App\Model\UserSecretKey;
use App\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeySecretMiddleware
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
        if (!empty($request->header('authorization'))) {
            return $next($request);
        } else {

            if(!empty($request->header('usersecretkey'))) {
                $userKey = UserSecretKey::where('secret_key', $request->header('usersecretkey'))->first();

                if ($userKey) {
                    $user = User::find($userKey->user_id);
                    $token = $user->createToken($user->email)->accessToken;

                    // Attach the access token to the request headers
                    $request->headers->set('Authorization', 'Bearer ' . $token);

                    define("USER_SECRET_DATA", $userKey);
                    define("IS_PUBLIC_API", true);
                    return $next($request);
                } else {
                    return response()->json(['success' => false, 'message' => __('Secret key expired or not found')]);
                }
            }
        }
        return $next($request);
    }
}
