<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Data\Paginate;
use Statamic\Facades\YAML;
use Symfony\Component\Finder\SplFileInfo;

class FileRepository extends BaseRepository
{
    public function __construct(
        protected string $path
    ) {
        File::ensureDirectoryExists($this->path);
    }

    public function get(): Enumerable
    {
        return collect(File::allFiles($this->path))
            ->filter(fn (SplFileInfo $file) => str($file->getFilename())->endsWith('.yaml'))
            ->map(fn (SplFileInfo $file) => $this->detourByFile($file))
            ->filter();
    }

    public function paginate(Paginate $paginate): LengthAwarePaginator
    {
        $paginate->validate();

        $collection = $this->get()->values();

        $total = $collection->count();
        $perPage = $paginate->size;

        $items = $collection
            ->slice(($paginate->page - 1) * $perPage, $perPage)
            ->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $paginate->page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function find(string $id): ?Detour
    {
        $file = $this->filePath($id);
        if (! File::exists($file)) {
            return null;
        }

        $data = YAML::parse(File::get($file));

        return Detour::make(['id' => $id, ...$data]);
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

    protected function detourByFile(SplFileInfo $file): ?Detour
    {
        $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

        return $this->find($id);
    }

    public static function bind(): void
    {
        app()->bind(FileRepository::class, function (Application $app): FileRepository {
            return new FileRepository(config()->string('justbetter.statamic-detour.path'));
        });
    }
}
