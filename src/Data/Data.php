<?php

namespace JustBetter\Detour\Data;

use Illuminate\Support\Fluent;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends Fluent<TKey, TValue>
 */
abstract class Data extends Fluent
{
    /** @var array<string, mixed> */
    protected array $rules = [];

    public function validate(): static
    {
        validator($this->attributes, $this->rules)->validate();

        return $this;
    }
}
