<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Facades\Storage;
use JustBetter\Detour\Contracts\ImportsDetour;
use JustBetter\Detour\Contracts\ImportsDetours;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportDetours implements ImportsDetours
{
    public function import(string $file): void
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');

        $file = Storage::disk($disk)->path($file);
        $rows = SimpleExcelReader::create($file)->getRows();
        $importDetour = app(ImportsDetour::class);

        // @phpstan-ignore-next-line argument.type
        $rows->each(function (array $row, int $index) use ($importDetour): void {
            try {
                $importDetour->import($row);
            } catch (\Throwable $e) {}
        });
    }

    public static function bind(): void
    {
        app()->singleton(ImportsDetours::class, static::class);
    }
}
