<?php

namespace JustBetter\Detour\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\Paginator;
use Illuminate\View\View;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\ListsDetours;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Http\Requests\IndexRequest;
use JustBetter\Detour\Http\Requests\StoreRequest;

class DetourController
{
    public function index(IndexRequest $request, ListsDetours $contract): mixed
    {
        $data = $contract->list($request->size ?? 15, Paginator::resolveCurrentPage());

        /** @var view-string $view */
        $view = 'statamic-detour::detours.index';

        return view($view, $data);
    }

    public function store(StoreRequest $request, StoresDetour $contract): JsonResponse
    {
        return response()->json(
            $contract->store(
                Form::make($request->validated())
            )
        );
    }

    public function destroy(string $id, DeletesDetour $contract): void
    {
        $contract->delete($id);
    }
}
