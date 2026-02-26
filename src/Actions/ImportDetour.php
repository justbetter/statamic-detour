<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ImportsDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Form;

class ImportDetour implements ImportsDetour
{
    public function __construct(protected ResolvesRepository $resolvesRepository) {}

    public function import(array $data): void
    {
        /** @var string $sites */
        $sites = $data['sites'] ?? '';
        $data['sites'] = $sites ? explode(';', $sites) : '';
        try {
            $data = Form::make($data)->validate();

            $repository = $this->resolvesRepository->resolve();
            if ($data->id) {
                $repository->update($data->id, $data);
            } else {
                $repository->store($data);
            }
        } catch (\Throwable $e) {
        }
    }

    public static function bind(): void
    {
        app()->singleton(ImportsDetour::class, static::class);
    }
}
