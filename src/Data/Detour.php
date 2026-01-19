<?php

namespace JustBetter\Detour\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Detour
{
    protected string $id;

    /** @var array<mixed, mixed> */
    protected array $data = [];

    public static function make(?string $id = null): self
    {
        $redirect = new self;
        $redirect->id = $id ?: (string) Str::uuid();

        return $redirect;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @param  array<mixed, mixed>  $data
     * @return array<mixed, mixed>
     */
    public function data(?array $data = null): array
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = $data ?? [];

        return $this->data;
    }

    /**
     * @param  mixed  $default
     */
    public function get(string $key, $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }
}
