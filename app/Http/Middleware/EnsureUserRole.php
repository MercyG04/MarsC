<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    

    // app/Http/Middleware/EnsureUserHasRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (! $request->user() || ! $request->user()->hasRole(...array_map(
        fn ($r) => UserRole::from($r), $roles
    ))) {
        abort(403);
    }

    return $next($request);
}
}
