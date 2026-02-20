<?php

namespace JustBetter\Detour\Actions;

use Illuminate\Support\Str;
use JustBetter\Detour\Contracts\GetsOldEntryUri;
use Statamic\Entries\Entry;

class GetOldEntryUri implements GetsOldEntryUri
{
    public function get(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): ?string
    {
        if (! $uri = $entry->uri()) {
            return null;
        }
        if (! $entry->published()) {
            return null;
        }

        $originalSlug = $entry->getOriginal('slug');
        $uriWithoutSlug = Str::beforeLast($uri, '/');
        $slug = is_string($originalSlug) && $originalSlug !== '' ? $originalSlug : $entry->slug();

        $oldUri = "$uriWithoutSlug/$slug";

        if ($parentOldSlug && $parentNewSlug) {
            $oldUri = str_replace($parentNewSlug, $parentOldSlug, $oldUri);
        }

        return $oldUri;
    }

    public static function bind(): void
    {
        app()->singleton(GetsOldEntryUri::class, static::class);
    }
}
