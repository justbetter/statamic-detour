<?php

namespace JustBetter\Detour\Utils;

use Illuminate\Support\Arr;
use Statamic\Entries\Entry;

class EntryHelper
{
    /** @return list<string> */
    public static function entryAndDescendantIds(Entry $entry): array
    {
        $ids = Arr::wrap($entry->id());

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
}
