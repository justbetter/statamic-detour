<?php

use Illuminate\Support\Facades\Route;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Http\Controllers\ImportExportController;

Route::prefix('detours')->group(function () {
    Route::get('/', [DetourController::class, 'index'])->name('justbetter.detours.index');

    Route::post('/store', [DetourController::class, 'store'])->name('justbetter.detours.store');

    Route::delete('/{detour}', [DetourController::class, 'destroy'])->name('justbetter.detours.destroy');

    Route::get('/actions', [ImportExportController::class, 'index'])->name('justbetter.detours.actions.index');
    Route::get('/actions/export', [ImportExportController::class, 'export'])->name('justbetter.detours.actions.export');

    Route::post('/actions/import', [ImportExportController::class, 'import'])->name('justbetter.detours.actions.import');
});
