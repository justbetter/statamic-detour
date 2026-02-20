<?php

namespace JustBetter\Detour\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromEvent;
use Statamic\Events\EntrySaved;

class EntrySavedListener
{
    public function __construct(
        protected CreatesDetoursFromEvent $contract,
    ) {}

    public function handle(EntrySaved $event): void
    {
        $this->contract->createFromEntrySaved($event);
    }
}
