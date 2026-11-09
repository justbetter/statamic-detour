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

    abstract public function firstWhere(string $field, mixed $value): ?Detour;

    /** @return Enumerable<array-key, Detour> */
    public function where(string $field, mixed $value): Enumerable
    {
        return $this->get()
            ->filter(fn (Detour $detour): bool => $detour->get($field) === $value)
            ->values();
    }

    abstract public function store(Form $form): Detour;

    abstract public function update(string $id, Form $form): Detour;

    abstract public function delete(string $id): void;
}
