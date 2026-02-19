<?php

namespace JustBetter\Detour\Utils;

use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry as EntryFacade;

class EntryHelper
{
    /** @return list<string> */
    public static function entryAndDescendantIds(Entry $entry): array
    {
        if (! $entry->id()) {
            return [];
        }

        $ids = [$entry->id()];

        if ($page = $entry->page()) {
            $ids = array_merge(
                $ids,
                $page->flattenedPages()
                    ->pluck('id')
                    ->filter()
                    ->map(fn (string $id): string => $id)
                    ->values()
                    ->all()
            );
        }

        /** @var list<string> $ids */
        return array_values(array_unique($ids));
    }

    /**
     * @param list<array<string, mixed>> $tree
     * @return list<Entry>
     */

    public static function treeToEntries(array $tree): array
    {
        $ids = [];

        foreach ($tree as $item) {
            $ids = array_merge($ids, static::gatherEntryIds($item));
        }

        $ids = array_values(array_unique($ids));

        foreach ($ids as $id) {
            Blink::forget('eloquent-entry-' . $id);
        }

        $entries = [];
        foreach ($ids as $id) {
            $entry = EntryFacade::find($id);
            if (! $entry instanceof Entry) {
                continue;
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @param array<string, mixed> $item
     * @return list<string>
     */
    public static function gatherEntryIds(array $item): array
    {
        $ids = [];

        if (isset($item['entry']) && is_string($item['entry'])) {
            $ids[] = $item['entry'];
        }

        if (! isset($item['children']) || ! is_array($item['children'])) {
            return $ids;
        }

        foreach ($item['children'] as $child) {
            if (! is_array($child)) {
                continue;
            }

            if (array_filter(array_keys($child), static fn ($key): bool => ! is_string($key)) !== []) {
                continue;
            }

            /** @var array<string, mixed> $child */
            $ids = array_merge($ids, static::gatherEntryIds($child));
        }

        /** @var list<string> $ids */
        return $ids;
    }
}
