<?php

namespace JustBetter\Detour\Listeners;

use Illuminate\Support\Facades\Log;
use JustBetter\Detour\Contracts\CreatesDetoursFromEvent;
use Statamic\Events\EntrySaved;

class EntrySavedListener
{
    public function __construct(
        protected CreatesDetoursFromEvent $contract,
    ) {}

    public function handle(EntrySaved $event): void
    {
        Log::info('IN ENTRY SAVED LISTENER');
        $this->contract->createFromEntry($event);
    }
}
