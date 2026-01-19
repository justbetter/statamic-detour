<?php

use Illuminate\Support\Facades\Route;
use JustBetter\Detour\Http\Controllers\DetourController;

Route::prefix('detours')->group(function () {
    Route::get('/', [DetourController::class, 'index'])->name('justbetter.detours.index');

    Route::post('/store', [DetourController::class, 'store'])->name('justbetter.detours.store');

    Route::delete('/{detour}', [DetourController::class, 'destroy'])->name('justbetter.detours.destroy');
});
