<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\DetourFilter;
use JustBetter\Detour\Data\Form;

abstract class BaseRepository
{
    /** @return array<string, Detour> */
    abstract public function get(?DetourFilter $filter = null): array;

    /** @return LengthAwarePaginator<string, Detour> */
    abstract public function paginate(int $perPage, ?int $page = null): LengthAwarePaginator;

    abstract public function find(string $id): ?Detour;

    abstract public function store(Form $form): Detour;

    abstract public function delete(string $id): void;
}
