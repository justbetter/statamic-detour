<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Events\EntrySaved;

interface CreatesDetoursFromEvent
{
    public function createFromEntrySaved(EntrySaved $event): void;
}
