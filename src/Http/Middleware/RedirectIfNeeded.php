<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JustBetter\Detour\Contracts\GeneratesUrl;
use JustBetter\Detour\Contracts\MatchesDetour;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNeeded
{
    public function __construct(
        protected MatchesDetour $matcher,
        protected GeneratesUrl $generator
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.ltrim($request->path(), '/');

        $detour = $this->matcher->match($path);

        if ($detour) {
            $url = $this->generator->generate($detour, $path);

            return $url ? redirect()->to($url, $detour->code) : $next($request);
        }

        return $next($request);
    }
}
