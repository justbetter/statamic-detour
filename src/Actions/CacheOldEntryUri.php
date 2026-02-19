<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use JustBetter\Detour\Contracts\CachesOldEntryUri;
use JustBetter\Detour\Contracts\ResolvesRepository;
use Statamic\Entries\Entry;

class CacheOldEntryUri implements CachesOldEntryUri {

    public function __construct(
        protected ResolvesRepository $resolvesRepository
    ) {}

    public function cache(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): void {
        if (! $uri = $entry->uri()) {
            return;
        }

        if (!$entry->published()) {
            return;
        }

        $originalSlug = $entry->getOriginal('slug');
        $uriWithoutSlug = Str::beforeLast($uri, '/');
        $slug = is_string($originalSlug) && $originalSlug !== '' ? $originalSlug : $entry->slug();

        $oldUri = "$uriWithoutSlug/$slug";

        if ($parentOldSlug && $parentNewSlug) {
            $oldUri = str_replace($parentNewSlug, $parentOldSlug, $oldUri);
        }

        Cache::put("redirect-entry-uri-before:{$entry->id()}", $oldUri, now()->addMinute());
    }

    public static function bind(): void
    {
        app()->singleton(CachesOldEntryUri::class, static::class);
    }

}
