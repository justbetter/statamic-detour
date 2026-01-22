<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Database\Eloquent\Builder;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Models\DetourFilter;

class EloquentRepository extends BaseRepository
{
    public function get(?DetourFilter $filter = null): array
    {
        $query = DetourModel::query();

        $normalizedPath = $filter->normalizedPath ?? null;

        if ($normalizedPath) {
            $query->where(function (Builder $query) use ($normalizedPath): void {
                $query
                    ->where(function (Builder $query) use ($normalizedPath): void {
                        $query->where('type', 'path')
                            ->where('from', $normalizedPath);
                    })
                    ->orWhere('type', 'regex');
            });
        }

        return $query->get()->mapWithKeys(fn (DetourModel $model) => [$model->id => Detour::make($model->toArray())])->all();
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
