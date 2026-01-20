<?php

namespace JustBetter\Detour\Contracts;

interface DetourContract
{
    public function id(): string;

    /**
     * @param  iterable<string, string | array<int, string>>  $attributes
     * @return static
     */
    public static function make($attributes = []);
}
