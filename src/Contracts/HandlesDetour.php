<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface HandlesDetour
{
    public function handle(string $normalizedPath): ?Detour;
}
