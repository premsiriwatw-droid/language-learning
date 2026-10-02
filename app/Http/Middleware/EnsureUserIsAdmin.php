<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Used after the 'auth' middleware, so $request->user() is always
     * present here - this only checks the admin flag.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) $request->user()?->is_admin, 403, 'Admins only.');

        return $next($request);
    }
}
