<?php

namespace JustBetter\Detour\Data;

use Illuminate\Support\Str;
use JustBetter\Detour\Contracts\DetourContract;
use JustBetter\Detour\Models\Detour as DetourModel;

class EloquentDetour extends BaseDetour implements DetourContract
{
    protected ?DetourModel $model;

    public static function fromModel(DetourModel $model): static
    {
        $detour = static::make($model->toArray());

        $detour->model($model);

        return $detour;
    }

    public function model(?DetourModel $model = null): ?DetourModel
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        return $this->model ??= $model;
    }

    public function id(): string
    {
        /** @var string $id */
        $id = $this->data('id') ?? Str::uuid()->toString();

        return $id;
    }
}
