<?php

namespace JustBetter\Detour\Listeners;

use Illuminate\Support\Facades\Log;
use JustBetter\Detour\Contracts\CreatesDetoursFromEvent;
use JustBetter\Detour\Utils\EntryHelper;
use Statamic\Events\CollectionTreeSaved;

class CollectionTreeSavedListener
{
    public function __construct(
        protected CreatesDetoursFromEvent $contract,
    ) {}

    public function handle(CollectionTreeSaved $event): void
    {
        $entries = EntryHelper::treeToEntries($event->tree->tree());

        foreach ($entries as $entry) {
            Log::info('COLLECTION TREE SAVED: ' . $entry->uri());
        }

//        $this->contract->createFromCollectionTree($event);
    }
}
