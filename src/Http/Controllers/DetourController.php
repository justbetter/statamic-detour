<?php

namespace JustBetter\Detour\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Contracts\DetourContract;
use Statamic\Fields\Blueprint;
use Statamic\Fields\BlueprintRepository;

class DetourController
{
    public function index(DetourRepositoryContract $contract): View
    {
        // @phpstan-ignore-next-line
        $oldDirectory = Blueprint::directory();
        $values = $contract->all();

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

    public function store(Request $request, DetourRepositoryContract $contract): JsonResponse
    {
        $blueprint = (new BlueprintRepository)->setDirectories(__DIR__.'/../../../resources/blueprints')->find('detour');
        $fields = $blueprint->fields();

        $data = $request->all();
        $detourContract = app(DetourContract::class);
        $detour = $detourContract::make();
        $fields = $fields->addValues($data);
        $fields->validate();

        /** @var DetourContract $detour */
        $detour = $detour->data($fields->validate());

        $contract->save($detour);

        return response()->json($detour);
    }

    public function destroy(string $detour, DetourRepositoryContract $contract): void
    {
        /** @var DetourContract $foundDetour */
        $foundDetour = $contract->find($detour);

        $contract->delete($foundDetour);
    }
}
