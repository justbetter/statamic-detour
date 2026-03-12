<?php

namespace JustBetter\Detour\Utils;

use Statamic\Entries\Entry;

class EntryHelper
{
    /** @return list<string> */
    public static function entryAndDescendantIds(Entry $entry): array
    {
        $ids = collect([$entry->id()])
            ->when(
                $entry->page(),
                fn ($collection, $page) => $collection->merge(
                    $page->flattenedPages()->pluck('id')
                )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();

        /** @var list<string> $ids */
        return $ids;
    }
}
