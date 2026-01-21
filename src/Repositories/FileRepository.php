<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
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
        return collect(File::allFiles($this->path))
            ->filter(fn (SplFileInfo $file): bool => str($file->getFilename())->endsWith('.yaml'))
            ->mapWithKeys(function (SplFileInfo $file): array {
                $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                return [$id => $this->find($id)];
            })
            ->filter()
            ->all();
    }

    public function find(string $id): ?Detour
    {
        $file = $this->filePath($id);
        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));
        $detour = Detour::make(['id' => $id, ...$data]);

        return $detour;
    }

    public function store(Form $form): Detour
    {
        $data = $form->toArray();
        $id = Str::uuid()->toString();
        $file = $this->filePath($id);

        File::ensureDirectoryExists($this->path);
        File::put($file, YAML::dump($data));

        return Detour::make(['id' => $id, ...$data]);
    }

    public function delete(string $id): void
    {
        $file = $this->filePath($id);

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
