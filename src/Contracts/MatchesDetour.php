<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface MatchesDetour
{
    public function match(string $path): ?Detour;
}
