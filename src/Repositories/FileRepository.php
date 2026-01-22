<?php

namespace JustBetter\Detour\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
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
        return $this->detours()->all();
    }

    public function paginate(int $perPage, ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage();
        $detours = $this->detours();

        return new LengthAwarePaginator(
            $detours->forPage($page, $perPage),
            $detours->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function findCandidates(string $normalizedPath): array
    {
        return $this->detours()
            ->filter(function (?Detour $detour) use ($normalizedPath): bool {
                if ($detour === null) {
                    return false;
                }

                if ($detour->type === 'regex') {
                    return true;
                }

                return '/'.ltrim($detour->from, '/') === $normalizedPath;
            })
            ->mapWithKeys(function (?Detour $detour): array {
                if ($detour === null) {
                    return [];
                }

                return [$detour->id => $detour];
            })
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

        if ($data['type'] === 'path') {
            $data['from'] = '/'.ltrim($form->from, '/');
            $data['to'] = '/'.ltrim($form->to, '/');
        }

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

    /**
     * @return Collection<string, Detour>
     */
    protected function detours(): Collection
    {
        return collect(File::allFiles($this->path))
            ->filter(fn (SplFileInfo $file) => str($file->getFilename())->endsWith('.yaml')
            )
            ->map(fn (SplFileInfo $file) => $this->detourByFile($file))
            ->filter();
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
