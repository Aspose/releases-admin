<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CanManageUsers
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
        if (!in_array(Auth::user()->is_admin, array(1,2)) ) { // 1 = superadmin 2 =admin 3== user
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated. You are not normal user.'], 401);
            }

            abort(403, "permission denied");
        }

        return $next($request);
    }
}
