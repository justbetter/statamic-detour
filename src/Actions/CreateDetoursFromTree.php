<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Collection;
use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use Spatie\Blink\Blink as SpatieBlink;
use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Statamic\Structures\CollectionTree;
use Statamic\Structures\Page;

class CreateDetoursFromTree implements CreatesDetoursFromTree
{
    /** @var array<string, array<string, string>> */
    protected array $pending = [];

    public function __construct(
        protected CreatesAutoDetour $createAutoDetourContract,
    ) {}

    // Runs while saving: the Stache syncs the tree's original state as soon as
    // it is fetched again, so the old uris are only reliable before the save.
    public function prepare(CollectionTree $tree): void
    {
        unset($this->pending[$this->key($tree)]);

        if (! config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        $collection = $tree->collection();

        if ($collection->orderable() || ! $collection->route($tree->locale())) {
            return;
        }

        /** @var array<int, mixed> $originalTree */
        $originalTree = $tree->getOriginal('tree', []);

        /** @var list<string> $ids */
        $ids = $tree->diff()->ancestryChanged();

        if ($ids === []) {
            return;
        }

        $oldUris = $this->uris((clone $tree)->tree($originalTree), $ids);
        $newUris = $this->uris(clone $tree, $ids);

        $this->flushUriCaches();

        $this->pending[$this->key($tree)] = $oldUris
            ->mapWithKeys(fn (string $oldUri, string $id): array => [$oldUri => $newUris->get($id)])
            ->filter()
            ->all();
    }

    public function create(CollectionTree $tree): void
    {
        $key = $this->key($tree);
        $uris = $this->pending[$key] ?? [];

        unset($this->pending[$key]);

        foreach ($uris as $oldUri => $newUri) {
            $this->createAutoDetourContract->create($oldUri, $newUri);
        }
    }

    /**
     * @param  list<string>  $ids
     * @return Collection<string, string>
     */
    protected function uris(CollectionTree $tree, array $ids): Collection
    {
        /** @var Collection<int, Page> $pages */
        $pages = $tree->disableUriCache()->pages()->flattenedPages();

        /** @var Collection<string, string> $uris */
        $uris = $pages
            ->filter(fn (Page $page): bool => in_array($page->reference(), $ids, true))
            ->filter(function (Page $page): bool {
                /** @var ?Entry $entry */
                $entry = $page->entry();

                return $entry !== null && $entry->published() && ! $entry->isRedirect();
            })
            ->mapWithKeys(fn (Page $page): array => [$page->reference() => $page->uri()])
            ->filter();

        return $uris;
    }

    // Page::uri() writes to these caches even with the uri cache disabled,
    // so computing the old uris would otherwise leak them into the request.
    protected function flushUriCaches(): void
    {
        foreach (['structure-uris', 'entry-uris'] as $name) {
            $store = Blink::store($name);

            if ($store instanceof SpatieBlink) {
                $store->flush();
            }
        }
    }

    protected function key(CollectionTree $tree): string
    {
        return $tree->handle().'::'.$tree->locale();
    }

    public static function bind(): void
    {
        app()->singleton(CreatesDetoursFromTree::class, static::class);
    }
}
