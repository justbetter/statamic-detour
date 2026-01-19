<?php

namespace JustBetter\Detour\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/** @implements CastsAttributes<array<int, string>, string|null> */
class CommaSeperatedToArray implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        /** @var string $value */
        return $value ? explode(',', $value) : [];
    }

    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
