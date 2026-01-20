<?php

namespace JustBetter\Detour\Repositories;

use JustBetter\Detour\Data\BaseDetour;
use JustBetter\Detour\Data\EloquentDetour;
use JustBetter\Detour\Models\Detour as DetourModel;

class EloquentRepository extends BaseRepository
{
    public function all(): array
    {
        /** @var array<string, EloquentDetour> $detours */
        $detours = collect(DetourModel::all())
            ->mapWithKeys(function (DetourModel $detour): array {
                return [$detour->id => $this->find($detour->id)];
            })
            ->filter()
            ->toArray();

        return $detours;
    }

    public function find(string $id): EloquentDetour
    {
        $model = DetourModel::findOrFail($id);

        $detour = EloquentDetour::fromModel($model);

        return $detour;
    }

    /**
     * @param  EloquentDetour  $detour
     */
    public function save(BaseDetour $detour): void
    {
        /** @var array<string, array<int, string>|string> $data */
        $data = $detour->getAttributes();
        $data = collect($data)->map(function ($value, $key) {
            return is_array($value) ? implode(',', $value) : $value;
        })->toArray();

        $model = DetourModel::updateOrCreate(['id' => $detour->id()], $data);

        $model = $detour->model($model);
    }

    /**
     * @param  EloquentDetour  $detour
     */
    public function delete(BaseDetour $detour): void
    {
        /** @var DetourModel $model */
        $model = $detour->model();
        $model->delete();
    }
}
