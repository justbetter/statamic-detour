<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\HandlesDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\DetourFilter;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class HandleDetour implements HandlesDetour
{
    public function __construct(protected ResolvesRepository $resolver) {}

    public function handle(string $path): ?Detour
    {
        $repository = $this->resolver->resolve();

        $filter = DetourFilter::make(['path' => $path]);
        $detours = $repository->get($filter);

        foreach ($detours as $detour) {
            if (! $this->appliesToCurrentSite($detour->sites)) {
                continue;
            }

            if (! $this->matchesRoute($detour->from, $path, $detour->type)) {
                continue;
            }

            return $detour;
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

        return in_array($currentSiteHandle, $sites);
    }

    protected function matchesRoute(string $from, string $normalizedPath, string $type): bool
    {
        if ($type === 'regex') {
            return preg_match($from, $normalizedPath) === 1;
        }

        return $from === $normalizedPath;
    }

    public static function bind(): void
    {
        app()->singleton(HandlesDetour::class, static::class);
    }
}
