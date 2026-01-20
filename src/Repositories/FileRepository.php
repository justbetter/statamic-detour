<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use JustBetter\Detour\Data\BaseDetour;
use JustBetter\Detour\Data\FileDetour;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class FileRepository extends BaseRepository
{
    public function __construct(
        protected string $path
    ) {
        File::ensureDirectoryExists($this->path);
    }

    public function all(): array
    {
        /** @var array<string, FileDetour> $detours */
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

    public function find(string $id): ?FileDetour
    {
        $file = $this->filePath($id);
        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));
        $detour = FileDetour::make(['id' => $id, ...$data]);

        return $detour;
    }

    public function save(BaseDetour $detour): void
    {
        $file = $this->filePath($detour->id());

        File::ensureDirectoryExists($this->path);
        File::put($file, YAML::dump($detour->getAttributes()));
    }

    public function delete(BaseDetour $detour): void
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

    public static function bind(): void
    {
        app()->bind(FileRepository::class, function (Application $app): FileRepository {
            return new FileRepository(config()->string('justbetter.statamic-detour.path'));
        });
    }
}
