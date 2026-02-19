<?php

namespace JustBetter\Detour\Listeners;

use JustBetter\Detour\Contracts\CachesOldEntryUri;
use JustBetter\Detour\Utils\EntryHelper;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Structures\CollectionTreeDiff;

class CacheOldUri
{
    public function __construct(protected CachesOldEntryUri $contract) {}

    public function handle(EntrySaving|CollectionTreeSaving $event): void
    {
        if (!config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        if ($event instanceof EntrySaving) {
            if (!$event->entry->id()) {
                return;
            }

            $parentOldSlug = $event->entry->getOriginal('slug');
            $parentNewSlug = $event->entry->slug();

            foreach (EntryHelper::entryAndDescendantIds($event->entry) as $entryId) {
                /** @var Entry|null $entry */
                $entry = EntryFacade::find($entryId);
                if (!$entry) {
                    continue;
                }

                if ($entry->id() === $event->entry->id()) {
                    $this->contract->cache($entry);
                } else {
                    $this->contract->cache($entry, $parentOldSlug, $parentNewSlug);
                }
            }

            return;
        }

        /** @var CollectionTreeDiff $diff */
        $diff = $event->tree->diff();

        foreach ($diff->affected() as $entryId) {
            /** @var Entry|null $entry */
            $entry = EntryFacade::find($entryId);
            if (!$entry) {
                continue;
            }

            $parentOldSlug = $entry->getOriginal('slug');
            $parentNewSlug = $entry->slug();

            foreach (EntryHelper::entryAndDescendantIds($entry) as $affectedId) {
                /** @var Entry|null $affectedEntry */
                $affectedEntry = EntryFacade::find($affectedId);
                if (!$affectedEntry) {
                    continue;
                }

                if ($affectedEntry->id() === $entry->id()) {
                    $this->contract->cache($affectedEntry);
                } else {
                    $this->contract->cache($affectedEntry, $parentOldSlug, $parentNewSlug);
                }

            }
        }
    }
}
