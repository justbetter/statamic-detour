<?php

namespace JustBetter\Detour\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromEntry;
use Statamic\Events\EntrySaved;

class EntrySavedListener
{
    public function __construct(
        protected CreatesDetoursFromEntry $contract,
    ) {}

    public function handle(EntrySaved $event): void
    {
        $this->contract->create($event->entry);
    }
}
