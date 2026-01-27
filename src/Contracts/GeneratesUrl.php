<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface GeneratesUrl
{
    public function generate(Detour $detour, string $path): string;
}
