<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Role;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (! $request->user()->hasPermissionTo($role)) {
             return redirect('/home');
        }

        return $next($request);
    }
}
