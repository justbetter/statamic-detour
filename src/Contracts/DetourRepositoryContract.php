<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface DetourRepositoryContract
{
    /**
     * @return array<int, Redirect>
     */
    public function all(): array;

    public function find(string $id): ?Detour;

    public function save(Detour $redirect): void;

    public function delete(Detour $redirect): void;
}
