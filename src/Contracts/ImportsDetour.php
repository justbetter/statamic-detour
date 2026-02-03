<?php

namespace JustBetter\Detour\Contracts;

interface ImportsDetour
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function import(array $data): void;
}
