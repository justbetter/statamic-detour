<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;

interface CreatesDetoursFromEvent
{
    public function createFromEntry(EntrySaved $event): void;

    public function createFromCollectionTree(CollectionTreeSaved $event): void;
}
