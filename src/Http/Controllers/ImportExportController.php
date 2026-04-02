<?php

namespace JustBetter\Detour\Http\Controllers;

use JustBetter\Detour\Contracts\ExportsDetours;
use JustBetter\Detour\Http\Requests\ImportRequest;
use JustBetter\Detour\Jobs\ImportDetours;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ImportExportController
{
    public function index(): mixed
    {
        /** @var view-string $view */
        $view = 'statamic-detour::detours.actions.index';

        return view($view);
    }

    public function export(ExportsDetours $contract): BinaryFileResponse
    {
        $file = $contract->export();

        return response()->download($file);
    }

    public function import(ImportRequest $request): RedirectResponse
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        /** @var string $file */
        $file = $request->file->store(options: $disk);
        ImportDetours::dispatch($file);

        return redirect()
            ->route('statamic.cp.justbetter.detours.actions.index')
            ->with('success', __('The import is being processed.'));
    }
}
