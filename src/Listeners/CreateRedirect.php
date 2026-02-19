<?php

namespace JustBetter\Detour\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Utils\EntryHelper;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry as EntryFacade;

class CreateRedirect
{
    public function __construct(
        protected StoresDetour  $storeContract,
        protected FindsDetour   $findContract,
        protected DeletesDetour $deleteContract
    ){}

    public function handle(EntrySaved|CollectionTreeSaved $event): void
    {
        if (!config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        if ($event instanceof EntrySaved) {
            foreach (EntryHelper::entryAndDescendantIds($event->entry) as $entryId) {
                /** @var Entry|null $entry */
                $entry = EntryFacade::find($entryId);
                if (!$entry) {
                    continue;
                }

                $this->createRedirect($entry);
            }

        } else {
            $this->createRedirect(EntryHelper::treeToEntries($event->tree->tree()));
        }
    }

    /** @param Entry|array<Entry> $entries */
    protected function createRedirect(Entry|array $entries): void
    {
        $entries = Arr::wrap($entries);

        foreach ($entries as $entry) {
            if (!$entry->uri()) {
                continue;
            }

            if ($entry->collection()->handle() !== 'pages') {
                continue;
            }

            if ($conflictingDetour = $this->findContract->findBy('from', $entry->uri())) {
                $this->deleteContract->delete($conflictingDetour->id);
            }

            if (!$oldUri = Cache::pull("redirect-entry-uri-before:{$entry->id()}")) {
                continue;
            }

            if ($entry->uri() === $oldUri) {
                continue;
            }

            $data = Form::make([
                'from' => $oldUri,
                'to' => $entry->uri(),
                'code' => '301',
                'type' => Type::Path->value,
            ]);

            $this->storeContract->store($data);
        }
    }
}
