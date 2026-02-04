<?php

namespace JustBetter\Detour\Contracts;

interface ImportsDetours
{
    public function import(string $file): void;
}
