<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Fields\Blueprint;

class ListDetours implements ListsDetours
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function list(): array
    {
        $repository = $this->resolvesRepository->resolve();

        // @phpstan-ignore-next-line
        $oldDirectory = Blueprint::directory();

        /** @var int $perPage */
        $perPage = config('justbetter.statamic-detour.per_page', 10);

        $paginated = $repository->paginate($perPage);
        $values = $paginated->items();

        // @phpstan-ignore-next-line
        $blueprint = Blueprint::setDirectories(__DIR__.'/../../resources/blueprints')->find('detour');
        $fields = $blueprint->fields();
        $fields = $fields->addValues($values);
        $fields = $fields->preProcess();

        if ($oldDirectory) {
            // @phpstan-ignore-next-line
            Blueprint::setDirectories($oldDirectory);
        }

        return [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'data' => $paginated,
            'action' => cp_route('justbetter.detours.store'),
        ];
    }

    public static function bind(): void
    {
        app()->singleton(ListsDetours::class, static::class);
    }
}
