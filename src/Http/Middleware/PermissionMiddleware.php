<?php

namespace Jimmy\Permissions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (! $request->user()?->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
