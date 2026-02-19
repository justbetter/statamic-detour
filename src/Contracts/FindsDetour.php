<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface FindsDetour
{
    public function findBy(string $field, mixed $value): ?Detour;
}
