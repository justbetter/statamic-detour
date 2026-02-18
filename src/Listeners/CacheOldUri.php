<?php

namespace JustBetter\Detour\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry;

class CacheOldUri
{
    public function handle(EntrySaving|CollectionTreeSaving $event): void
    {
        if (! config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        if ($event instanceof EntrySaving) {
            if (! $event->entry->id()) {
                return;
            }

            $this->cacheEntryUri($event->entry->id());

            return;
        }

        /** @var \Statamic\Structures\CollectionTreeDiff $diff */
        $diff = $event->tree->diff();

        foreach ($diff->affected() as $entry) {
            $this->cacheEntryUri($entry);
        }
    }

    protected function cacheEntryUri(string $entryId): void
    {
        $entry = Entry::find($entryId);

        if (! $entry || ! $uri = $entry->uri()) {
            return;
        }

        if (! $entry->published()) {
            return;
        }

        $originalSlug = $entry->getOriginal('slug');
        $uriWithoutSlug = Str::beforeLast($uri, '/');
        $slug = is_string($originalSlug) && $originalSlug !== '' ? $originalSlug : $entry->slug();

        $oldUri = "{$uriWithoutSlug}/{$slug}";

        Cache::put("redirect-entry-uri-before:{$entry->id()}", $oldUri, now()->addMinute());
    }
}
