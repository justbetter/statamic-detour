<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Fields\Blueprint;

class ListDetours implements ListsDetours
{
    public function __construct(
        protected ResolvesRepository $respositoryContract
    ) {}

    public function list(): array
    {
        $repository = $this->respositoryContract->resolve();

        // @phpstan-ignore-next-line
        $oldDirectory = Blueprint::directory();
        $values = $repository->all();

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
            'data' => $values,
            'action' => cp_route('justbetter.detours.store'),
        ];
    }

    public static function bind(): void
    {
        app()->singleton(ListsDetours::class, static::class);
    }
}
