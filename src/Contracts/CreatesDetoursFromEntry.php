<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Entries\Entry;

interface CreatesDetoursFromEntry
{
    public function create(Entry $entry): void;
}
