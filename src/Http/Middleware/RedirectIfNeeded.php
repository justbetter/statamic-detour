<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNeeded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // logic by Niek
        // ophalen detours huidige site en huidige path OF regex
        // redirect naar detour

        return $next($request);
    }
}
