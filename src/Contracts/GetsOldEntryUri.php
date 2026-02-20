<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Entries\Entry;

interface GetsOldEntryUri
{
    public function get(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): ?string;
}
