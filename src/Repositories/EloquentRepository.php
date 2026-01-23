<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Database\Eloquent\Builder;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\DetourFilter;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Models\Detour as DetourModel;

class EloquentRepository extends BaseRepository
{
    public function get(?DetourFilter $filter = null): array
    {
        return DetourModel::query()
            ->when($filter, function (Builder $query, DetourFilter $filter) {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where(function (Builder $query) use ($filter): void {
                        $query->where('type', 'path')
                            ->where('from', $filter->path);
                    })
                        ->orWhere('type', 'regex');
                });
            })
            ->get()
            ->mapWithKeys(fn (DetourModel $model) => [$model->id => Detour::make($model->toArray())])
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
