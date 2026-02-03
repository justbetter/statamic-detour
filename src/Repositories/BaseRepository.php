<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Enumerable;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Data\Paginate;

abstract class BaseRepository
{
    /** @return Enumerable<int, Detour> */
    abstract public function get(): Enumerable;

    /** @return LengthAwarePaginator<int, Detour> */
    abstract public function paginate(Paginate $paginate): LengthAwarePaginator;

    abstract public function find(string $id): ?Detour;

    abstract public function store(Form $form): Detour;

    abstract public function update(string $id, Form $form): Detour;

    abstract public function delete(string $id): void;
}
