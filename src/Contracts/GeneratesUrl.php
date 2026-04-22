<?php

namespace JustBetter\Detour\Contracts;

use JustBetter\Detour\Data\Detour;

interface GeneratesUrl
{
    /**
     * @param  array<string, mixed>  $queryParameters
     */
    public function generate(Detour $detour, string $path, array $queryParameters = []): ?string;
}
