<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;

class FindDetour implements FindsDetour
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function firstWhere(string $field, mixed $value): ?Detour
    {
        $repository = $this->resolvesRepository->resolve();

        return $repository->firstWhere($field, $value);
    }

    public static function bind(): void
    {
        app()->singleton(FindsDetour::class, static::class);
    }
}
