<?php

namespace JustBetter\Detour\Contracts;

use Illuminate\Support\Enumerable;
use JustBetter\Detour\Data\Detour;

interface FindsDetour
{
    public function firstWhere(string $field, mixed $value): ?Detour;

    /** @return Enumerable<array-key, Detour> */
    public function where(string $field, mixed $value): Enumerable;
}
