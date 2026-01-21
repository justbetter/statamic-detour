<?php

namespace JustBetter\Detour\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNeeded
{
    public function handle(Request $request, Closure $next): Response
    {
        $repository = app(ResolvesRepository::class)->resolve();
        $detours = $repository->all();

        $normalizedPath = '/'.ltrim($request->path(), '/');

        /** @var array<int, array{
         *   from: string,
         *   to: string,
         *   type: string,
         *   code: int|string,
         *   sites?: array<int, string>|null
         * }> $detours
         */
        foreach ($detours as $detour) {
            if (! $this->shouldApplyToCurrentSite($detour['sites'] ?? null)) {
                continue;
            }

            if (! $this->matchesRoute($detour['from'], $normalizedPath, $detour['type'])) {
                continue;
            }

            return redirect()->to($detour['to'], (int) $detour['code']);
        }

        return $next($request);
    }

    protected function matchesRoute(string $from, string $normalizedPath, string $type): bool
    {
        if ($type === 'regex') {
            return $this->matchesPattern($from, $normalizedPath);
        }

        $normalizedFrom = '/'.ltrim($from, '/');

        return $normalizedFrom === $normalizedPath;
    }

    /**
     * @param  array<int, string>|null  $sites
     */
    protected function shouldApplyToCurrentSite(?array $sites): bool
    {
        if (empty($sites)) {
            return true;
        }

        /** @var Site $site */
        $site = SiteFacade::current();
        $currentSiteHandle = $site->handle();

        return in_array($currentSiteHandle, $sites, true);
    }

    protected function matchesPattern(string $pattern, string $currentPath): bool
    {
        try {
            return preg_match($pattern, $currentPath) === 1;
        } catch (\Throwable) {
            return false;
        }
    }
}
