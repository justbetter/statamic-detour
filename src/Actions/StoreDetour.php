<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;

class StoreDetour implements StoresDetour
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function store(Form $form): Detour
    {
        $repository = $this->resolvesRepository->resolve();

        return $repository->store($form);
    }

    public static function bind(): void
    {
        app()->singleton(StoresDetour::class, static::class);
    }
}
