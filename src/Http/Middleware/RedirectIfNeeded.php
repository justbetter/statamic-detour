<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JustBetter\Detour\Contracts\HandlesDetour;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNeeded
{
    public function __construct(
        private HandlesDetour $handler
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($response = $this->handler->resolveRedirect($request)) {
            return $response;
        }

        return $next($request);
    }
}
