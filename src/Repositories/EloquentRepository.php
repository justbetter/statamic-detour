<?php

namespace JustBetter\Detour\Repositories;

use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Models\Detour as DetourModel;

class EloquentRepository extends BaseRepository
{
    public function all(): array
    {
        return DetourModel::query()
            ->get()
            ->mapWithKeys(function (DetourModel $detour): array {
                return [$detour->id => Detour::make($detour->toArray())];
            })
            ->all();
    }

    public function find(string $id): Detour
    {
        $model = DetourModel::findOrFail($id);

        return Detour::make($model->toArray());
    }

    public function store(Form $form): Detour
    {
        $data = $form->toArray();

        $model = DetourModel::query()->create($data);

        return Detour::make($model->toArray());
    }

    public function delete(string $id): void
    {
        $model = DetourModel::findOrFail($id);

        $model->delete();
    }
}
