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
        $normalizedPath = '/'.ltrim($request->path(), '/');

        $applicableDetour = $this->handler->handle($normalizedPath);

        if ($applicableDetour) {
            return redirect()->to($applicableDetour->to, $applicableDetour->code);
        }

        return $next($request);
    }
}
