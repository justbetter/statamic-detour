<?php

use Illuminate\Support\Facades\Route;
use JustBetter\Detour\Http\Controllers\DetourController;
use JustBetter\Detour\Http\Controllers\ImportExportController;
use JustBetter\Detour\Http\Controllers\SettingsController;

Route::prefix('detours')
    ->middleware('detours.access')
    ->group(function () {
        Route::get('/', [DetourController::class, 'index'])->name('justbetter.detours.index');

        Route::post('/store', [DetourController::class, 'store'])->name('justbetter.detours.store');

        Route::delete('/{detour}', [DetourController::class, 'destroy'])->name('justbetter.detours.destroy');

        Route::get('/actions', [ImportExportController::class, 'index'])->name('justbetter.detours.actions.index');
        Route::get('/actions/export', [ImportExportController::class, 'export'])->name('justbetter.detours.actions.export');

        Route::post('/actions/import', [ImportExportController::class, 'import'])->name('justbetter.detours.actions.import');

        Route::get('/settings', [SettingsController::class, 'index'])->name('justbetter.detours.settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('justbetter.detours.settings.update');
    });
