<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Enumerable;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Data\Paginate;
use JustBetter\Detour\Models\Detour as DetourModel;

class EloquentRepository extends BaseRepository
{
    public function get(): Enumerable
    {
        return DetourModel::query()
            ->lazy()
            ->map(fn (DetourModel $model): Detour => Detour::make($model->toArray()));
    }

    public function paginate(Paginate $paginate): LengthAwarePaginator
    {
        return DetourModel::query()
            ->paginate($paginate->size, page: $paginate->page)
            ->through(fn (DetourModel $model): Detour => Detour::make($model->toArray()));
    }

    public function find(string $id): Detour
    {
        $model = DetourModel::query()->findOrFail($id);

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
        $model = DetourModel::query()->findOrFail($id);
        $model->delete();
    }
}
