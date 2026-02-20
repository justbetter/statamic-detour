<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\CreatesDetoursFromEvent;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\GetsOldEntryUri;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Utils\EntryHelper;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry as EntryFacade;

class CreateDetoursFromEvent implements CreatesDetoursFromEvent
{
    public function __construct(
        protected FindsDetour     $findContract,
        protected StoresDetour    $storeContract,
        protected DeletesDetour   $deleteContract,
        protected GetsOldEntryUri $getOldEntryUriContract,
    ) {}

    public function createFromEntry(EntrySaved $event): void
    {
        if (!config()->boolean('justbetter.statamic-detour.auto_create')) {
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

            $this->createDetour($entry, $parentOldSlug, $parentNewSlug);
        }
    }

    public function createFromCollectionTree(CollectionTreeSaved $event): void
    {
        if (!config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        $entries = EntryHelper::treeToEntries($event->tree->tree());

        foreach ($entries as $entry) {
            $this->createDetour($entry);
        }
    }

    protected function createDetour(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): void
    {
        if (!$entry->uri()) {
            return;
        }

        if ($conflictingDetour = $this->findContract->findBy('from', $entry->uri())) {
            $this->deleteContract->delete($conflictingDetour->id);
        }

        if ($parentOldSlug && $parentNewSlug) {
            $oldUri = $this->getOldEntryUriContract->get($entry, $parentOldSlug, $parentNewSlug);
        } else {
            $oldUri = $this->getOldEntryUriContract->get($entry);
        }

        if (!$oldUri) {
            return;
        }

        if ($entry->uri() === $oldUri) {
            return;
        }

        $data = Form::make([
            'from' => $oldUri,
            'to' => $entry->uri(),
            'code' => '301',
            'type' => Type::Path->value,
        ]);

        $this->storeContract->store($data);
    }

    public static function bind(): void
    {
        app()->singleton(CreatesDetoursFromEvent::class, static::class);
    }
}
