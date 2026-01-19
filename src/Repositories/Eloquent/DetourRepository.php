<?php

namespace JustBetter\Detour\Repositories\Eloquent;

use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Contracts\DetourContract;

class DetourRepository implements DetourRepositoryContract
{
    public function all(): array
    {
        return [];
    }

    public function find(string $id): ?DetourContract
    {
        return null;
    }

    public function save(DetourContract $detour): void
    {
    }

    public function delete(DetourContract $detour): void
    {
    }
}