<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
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
        if (Auth::user()->is_admin != 1) { // 1 = superadmin 2 =admin 3== user
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated. You are not super admin.'], 401);
            }

            abort(403, "permission denied -- " .Auth::user()->is_admin );
        }

        return $next($request);
    }
}
