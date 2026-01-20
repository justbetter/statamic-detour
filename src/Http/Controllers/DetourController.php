<?php

namespace JustBetter\Detour\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JustBetter\Detour\Contracts\DetourContract;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\BaseDetour;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;

class DetourController
{
    public function __construct(
        protected ResolvesRepository $repositoryContract
    ) {}

    public function index(): View
    {
        $repository = $this->repositoryContract->resolve();

        // @phpstan-ignore-next-line
        $oldDirectory = Blueprint::directory();
        $values = $repository->all();

        // @phpstan-ignore-next-line
        $blueprint = Blueprint::setDirectories(__DIR__.'/../../../resources/blueprints')->find('detour');
        $fields = $blueprint->fields();
        $fields = $fields->addValues($values);
        $fields = $fields->preProcess();

        if ($oldDirectory) {
            // @phpstan-ignore-next-line
            Blueprint::setDirectories($oldDirectory);
        }

        /** @var view-string $view */
        $view = 'statamic-detour::detours.index';

        return view($view, [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'data' => $values,
            'action' => cp_route('justbetter.detours.store'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $repository = $this->repositoryContract->resolve();

        $blueprint = (new BlueprintRepository)->setDirectories(__DIR__.'/../../../resources/blueprints')->find('detour');
        $fields = $blueprint->fields();

        $data = $request->all();

        $detourContract = app(DetourContract::class);

        $fields = $fields->addValues($data);
        $fields->validate();

        /** @var BaseDetour $detour */
        $detour = $detourContract::make($data);

        $repository->save($detour);

        return response()->json($detour);
    }

    public function destroy(string $detour): void
    {
        $repository = $this->repositoryContract->resolve();

        /** @var BaseDetour $foundDetour */
        $foundDetour = $repository->find($detour);

        $repository->delete($foundDetour);
    }
}
