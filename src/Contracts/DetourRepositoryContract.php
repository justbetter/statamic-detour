<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface DetourRepositoryContract
{
    /**
     * @return array<int, Detour>
     */
    public function all(): array;

    public function find(string $id): ?Detour;

    public function save(Detour $detour): void;

    public function delete(Detour $detour): void;
}
