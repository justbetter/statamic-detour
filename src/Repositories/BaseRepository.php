<?php

namespace JustBetter\Detour\Repositories;

use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;

abstract class BaseRepository
{
    /**
     * @return array<string, Detour>
     */
    abstract public function all(): array;

    abstract public function find(string $id): ?Detour;

    abstract public function store(Form $form): Detour;

    abstract public function delete(string $id): void;
}
