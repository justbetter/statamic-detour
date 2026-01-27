<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface ListsDetours
{
    /**
     * @return array{
     *     blueprint: array<string, mixed>,
     *     values: array<string, mixed>,
     *     meta: array<string, mixed>,
     *     data: Detour[],
     *     action: string
     * }
     */
    public function list(int $size, int $page): array;
}
