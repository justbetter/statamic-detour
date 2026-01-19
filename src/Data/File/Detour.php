<?php

namespace JustBetter\Detour\Data\File;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonSerializable;
use JustBetter\Detour\Contracts\DetourContract;

class Detour implements JsonSerializable, DetourContract
{
    protected string $id;

    /** @var array<string, string | array<int, string>> */
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
     * @param  array<string, string | array<int, string>>  $data
     * @return array<string, string | array<int, string>> | static
     */
    public function data(?array $data = null): array|static
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = $data ?? [];

        return $this;
    }

    /**
     * @param  mixed  $default
     */
    public function get(string $key, $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * @return array<string, string | array<int, string>>
     */
    public function jsonSerialize(): array
    {
        /** @var array<string, string | array<int, string>> $data */
        $data = $this->data();

        return [
            'id' => $this->id(),
            ...$data,
        ];
    }
}
