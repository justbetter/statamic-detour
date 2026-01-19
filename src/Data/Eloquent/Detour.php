<?php

namespace JustBetter\Detour\Data\Eloquent;

use Illuminate\Support\Str;
use JsonSerializable;
use JustBetter\Detour\Contracts\DetourContract;
use JustBetter\Detour\Models\Detour as DetourModel;

class Detour implements DetourContract, JsonSerializable
{
    protected ?DetourModel $model;

    protected string $id;

    /** @var array<string, string | array<int, string>> */
    protected array $data = [];

    public static function make(?string $id = null): self
    {
        $detour = new self;
        $detour->id = $id ?: Str::uuid();

        return $detour;
    }

    public function model(?DetourModel $model = null): static|DetourModel
    {
        if (func_num_args() === 0 && $this->model) {
            return $this->model;
        }

        $this->model = $model;

        if (! is_null($model)) {
            $this->id = $model->id;
        }

        return $this;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function data(?array $data = null): array|static
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = $data ?? [];

        return $this;
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
