<?php

namespace JustBetter\Detour\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use JustBetter\Detour\Data\Detour;

interface ListsDetours
{
    /**
     * @return array{
     *     blueprint: array<string, mixed>,
     *     values: array<string, mixed>,
     *     meta: array<string, mixed>,
     *     data: LengthAwarePaginator<string, Detour>,
     *     action: string
     * }
     */
    public function list(): array;
}
