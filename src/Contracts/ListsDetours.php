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
     *     data: Detour[],
     *     action: string,
     *     paginator: LengthAwarePaginator<int, Detour>
     * }
     */
    public function list(int $size, int $page, ?string $search = null): array;
}
