<?php

namespace JustBetter\Detour\Repositories\File;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Contracts\RedirectRepositoryContract;
use JustBetter\Detour\Data\Redirect;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class RedirectRepository implements RedirectRepositoryContract
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
            ->map(function (SplFileInfo $file): ?Redirect {
                $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                return $this->find($id);
            })
            ->filter()
            ->toArray();

        return $redirects;
    }

    public function find(string $id): ?Redirect
    {
        $file = $this->filePath($id);

        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));

        $redirect = Redirect::make($id);

        $redirect->data($data);

        return $redirect;
    }

    public function save(Redirect $redirect): void
    {
        $file = $this->filePath($redirect->id());

        File::ensureDirectoryExists($this->path);
        File::put($file, YAML::dump($redirect->data()));
    }

    public function delete(Redirect $redirect): void
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
