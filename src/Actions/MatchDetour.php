<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\MatchesDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Sites\Site;

class MatchDetour implements MatchesDetour
{
    public function __construct(protected ResolvesRepository $resolver) {}

    public function match(string $path): ?Detour
    {
        $repository = $this->resolver->resolve();

        /** @var Site $site */
        $site = SiteFacade::current();

        $detours = $repository->get();

        foreach ($detours as $detour) {
            if ($detour->matches($site->handle(), $path)) {
                return $detour;
            }
        }

        return null;
    }

    public static function bind(): void
    {
        app()->singleton(MatchesDetour::class, static::class);
    }
}
