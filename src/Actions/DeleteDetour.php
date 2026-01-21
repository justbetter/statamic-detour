<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;

class DeleteDetour implements DeletesDetour
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function delete(string $id): void
    {
        $repository = $this->resolvesRepository->resolve();

        $repository->delete($id);
    }

    public static function bind(): void
    {
        app()->singleton(DeletesDetour::class, static::class);
    }
}
