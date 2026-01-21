<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JustBetter\Detour\Contracts\HandlesDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class HandleDetour implements HandlesDetour
{
    public function __construct(private ResolvesRepository $resolver) {}

    public function resolveRedirect(Request $request): ?RedirectResponse
    {
        $repository = $this->resolver->resolve();
        $detours = $repository->all();

        $normalizedPath = '/'.ltrim($request->path(), '/');

        foreach ($detours as $detour) {
            if (! $this->appliesToCurrentSite($detour->sites)) {
                continue;
            }

            if (! $this->matchesRoute($detour->from, $normalizedPath, $detour->type)) {
                continue;
            }

            return redirect()->to($detour->to, $detour->code);
        }

        return null;
    }

    /**
     * @param  array<int, string>|null  $sites
     */
    protected function appliesToCurrentSite(?array $sites): bool
    {
        if (empty($sites)) {
            return true;
        }

        /** @var Site $site */
        $site = SiteFacade::current();
        $currentSiteHandle = $site->handle();

        return $currentSiteHandle !== null && in_array($currentSiteHandle, $sites, true);
    }

    protected function matchesRoute(string $from, string $normalizedPath, string $type): bool
    {
        if ($type === 'regex') {
            return @preg_match($from, $normalizedPath) === 1;
        }

        return '/'.ltrim($from, '/') === $normalizedPath;
    }

    public static function bind(): void
    {
        app()->singleton(HandlesDetour::class, static::class);
    }
}
