<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Entries\Entry;

interface CachesOldEntryUri {
    public function cache(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): void;
}
