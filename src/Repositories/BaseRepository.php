<?php

namespace JustBetter\Detour\Repositories;

use JustBetter\Detour\Data\BaseDetour;

abstract class BaseRepository
{
    /**
     * @return array<string, BaseDetour>
     */
    abstract public function all(): array;

    abstract public function find(string $id): ?BaseDetour;

    abstract public function save(BaseDetour $detour): void;

    abstract public function delete(BaseDetour $detour): void;
}
