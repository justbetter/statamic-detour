<?php

namespace JustBetter\Detour\Repositories\File;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Contracts\DetourContract;
use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\File\Detour;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class DetourRepository implements DetourRepositoryContract
{
    public function __construct(protected string $path)
    {
        File::ensureDirectoryExists($this->path);
    }

    public function all(): array
    {
        /** @var array<string, DetourContract> $detours */
        $detours = collect(File::allFiles($this->path))
            ->filter(fn (SplFileInfo $file): bool => str($file->getFilename())->endsWith('.yaml'))
            ->mapWithKeys(function (SplFileInfo $file): array {
                $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                return [$id => $this->find($id)];
            })
            ->filter()
            ->toArray();

        return $detours;
    }

    public function find(string $id): ?Detour
    {
        $file = $this->filePath($id);
        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));
        $detour = Detour::make($id);

        $detour->data($data);

        return $detour;
    }

    public function save(DetourContract $detour): void
    {
        $file = $this->filePath($detour->id());

        File::ensureDirectoryExists($this->path);
        File::put($file, YAML::dump($detour->data()));
    }

    public function delete(DetourContract $detour): void
    {
        $file = $this->filePath($detour->id());

        if (File::exists($file)) {
            File::delete($file);
        }
    }

    protected function filePath(string $id): string
    {
        return rtrim($this->path, '/')."/{$id}.yaml";
    }
}
