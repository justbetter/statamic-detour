<?php

namespace JustBetter\Detour\Models;

class DetourFilter
{
    public function __construct(
        public ?string $normalizedPath = null
    ) {}

}
