<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Contracts\DetourContract;

interface DetourRepositoryContract
{
    /**
     * @return array<int, Detour>
     */
    public function all(): array;

    public function find(string $id): ?DetourContract;

    public function save(DetourContract $detour): void;

    public function delete(DetourContract $detour): void;
}
