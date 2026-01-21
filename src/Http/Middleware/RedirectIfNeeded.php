<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Facades\Site;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        $repository = app(ResolvesRepository::class)->resolve();
        $detours = $repository->all();

        foreach ($detours as $detour) {
            if (! $this->matchesRoute($detour['from'], $request->path(), $detour['type'])) {
                continue;
            }

            if (! $this->includesCurrentSide($detour['sites'])) {
                continue;
            }

            return redirect()->to($detour['to'], (int) $detour['code']);
        }

        return $next($request);
    }

    protected function matchesRoute(string $from, string $currentPath, string $type): bool {
        $normalizedPath = '/'.ltrim($currentPath, '/');

        if ($type === 'regex') {
            return $this->matchesPattern($from, $normalizedPath);
        }

        $normalizedFrom = '/' . ltrim($from, '/');

        return $normalizedFrom === $normalizedPath;
    }

    protected function includesCurrentSide(array $sites): bool {
        if (empty($sites)) {
            return true;
        }

        $currentSiteHandle = Site::current()?->handle();
        return in_array($currentSiteHandle, $sites, true);
    }

    protected function matchesPattern(string $pattern, string $currentPath): bool {
        return preg_match($pattern, $currentPath) === 1;
    }
}
