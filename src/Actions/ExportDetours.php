<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JustBetter\Detour\Contracts\ExportsDetours;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportDetours implements ExportsDetours
{
    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function export(): string
    {
        $disk = config()->string('justbetter.statamic-detour.actions.disk');
        $filename = 'export.csv';
        $fullPath = Storage::disk($disk)->path($filename);
        File::ensureDirectoryExists(Storage::disk($disk)->path(''));

        $repository = $this->resolvesRepository->resolve();
        $detours = $repository->get();

        $detours = $detours->map(function (Detour $detour): array {
            $detour = $detour->toArray();
            /** @var array<int, string> $sites */
            $sites = $detour['sites'] ?? [];
            $detour['sites'] = isset($detour['sites']) ? implode(';', $sites) : null;

            return $detour;
        });

        $headers = $detours->flatMap(fn (array $detour): array => array_keys($detour))->unique()->toArray();

        $writer = SimpleExcelWriter::create($fullPath)->addHeader($headers);

        $writer->addRows($detours->toArray());

        $writer->close();

        return $fullPath;
    }

    public static function bind(): void
    {
        app()->singleton(ExportsDetours::class, static::class);
    }
}
