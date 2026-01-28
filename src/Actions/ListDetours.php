<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Paginate;
use Statamic\Fields\Blueprint;

class ListDetours implements ListsDetours
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function list(int $size, int $page): array
    {
        $repository = $this->resolvesRepository->resolve();

        // @phpstan-ignore-next-line
        $oldDirectory = Blueprint::directory();

        $paginate = Paginate::make(['size' => $size,  'page' => $page])->validate();

        $paginator = $repository->paginate($paginate);

        // @phpstan-ignore-next-line
        $blueprint = Blueprint::setDirectories(__DIR__.'/../../resources/blueprints')->find('detour');
        $fields = $blueprint->fields();
        $fields = $fields->addValues($paginator->items());
        $fields = $fields->preProcess();

        if ($oldDirectory) {
            // @phpstan-ignore-next-line
            Blueprint::setDirectories($oldDirectory);
        }

        return [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'data' => $paginator->items(),
            'action' => cp_route('justbetter.detours.store'),
            'paginator' => $paginator,
        ];
    }

    public static function bind(): void
    {
        app()->singleton(ListsDetours::class, static::class);
    }
}
