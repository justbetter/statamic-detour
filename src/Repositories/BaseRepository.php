<?php

namespace JustBetter\Detour\Repositories;

use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Models\DetourFilter;

abstract class BaseRepository
{
    /** @return array<string, Detour> */
    abstract public function get(?DetourFilter $filter = null): array;

    abstract public function find(string $id): ?Detour;

    abstract public function store(Form $form): Detour;

    abstract public function delete(string $id): void;
}
