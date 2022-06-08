<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::user()->is_admin != 3) { // 1 = superadmin 2 =admin 3== user
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated. You are not normal user.'], 401);
            }

            abort(403, "permission denied");
        }

        return $next($request);
    }
}
