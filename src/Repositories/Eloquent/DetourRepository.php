<?php

namespace JustBetter\Detour\Repositories\Eloquent;

use JustBetter\Detour\Contracts\DetourContract;
use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\Eloquent\Detour;
use JustBetter\Detour\Models\Detour as DetourModel;

class DetourRepository implements DetourRepositoryContract
{
    public function __construct(protected string $path) {}

    public function all(): array
    {
        /** @var array<string, DetourContract> $detours */
        $detours = collect(DetourModel::all())
            ->mapWithKeys(function (DetourModel $detour): array {
                return [$detour->id => $this->find($detour->id)];
            })
            ->filter()
            ->toArray();

        return $detours;
    }

    public function find(string $id): ?DetourContract
    {
        /** @var Detour $contract */
        $contract = app(DetourContract::class);

        $model = DetourModel::findOrFail($id);

        $detour = $contract->model($model);

        /** @var array<string, array<int, string>|string> $data */
        $data = $model->toArray();
        /** @var DetourContract $detour */
        $detour = $contract->data($data);

        return $detour;
    }

    /**
     * @param  Detour  $detour
     */
    public function save(DetourContract $detour): void
    {
        /** @var array<string, array<int, string>|string> $data */
        $data = $detour->data();
        $data = collect($data)->map(function ($value, $key) {
            return is_array($value) ? implode(',', $value) : $value;
        })->toArray();

        $model = DetourModel::updateOrCreate(['id' => $detour->id()], $data);

        $model = $detour->model($model);
    }

    /**
     * @param  Detour  $detour
     */
    public function delete(DetourContract $detour): void
    {
        /** @var DetourModel $model */
        $model = $detour->model();
        $model->delete();
    }
}
