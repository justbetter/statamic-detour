<?php

namespace JustBetter\Detour\Contracts;

interface CreatesAutoDetour
{
    public function create(string $fromUri, string $toUri, string $site): void;
}
