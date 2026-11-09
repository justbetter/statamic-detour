<?php

namespace JustBetter\Detour\Contracts;

interface CreatesAutoDetour
{
    public function create(string $from, string $to): void;
}
