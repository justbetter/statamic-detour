<?php

namespace JustBetter\Detour\Repositories\File;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\Detour;
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
        /** @var array<int, Redirect> $redirects */
        $redirects = collect(File::allFiles($this->path))
            ->filter(fn (SplFileInfo $file): bool => str($file->getFilename())->endsWith('.yaml'))
            ->map(function (SplFileInfo $file): ?Detour {
                $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                return $this->find($id);
            })
            ->filter()
            ->toArray();

        return $redirects;
    }

    public function find(string $id): ?Detour
    {
        $file = $this->filePath($id);

        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));

        $redirect = Detour::make($id);

        $redirect->data($data);

        return $redirect;
    }

    public function save(Detour $redirect): void
    {
        $file = $this->filePath($redirect->id());

        File::ensureDirectoryExists($this->path);
        File::put($file, YAML::dump($redirect->data()));
    }

    public function delete(Detour $redirect): void
    {
        $file = $this->filePath($redirect->id());

        if (File::exists($file)) {
            File::delete($file);
        }
    }

    protected function filePath(string $id): string
    {
        return rtrim($this->path, '/')."/{$id}.yaml";
    }
}
