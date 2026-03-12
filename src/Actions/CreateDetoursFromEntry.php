<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\CreatesDetoursFromEntry;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\GetsOldEntryUri;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Utils\EntryHelper;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class CreateDetoursFromEntry implements CreatesDetoursFromEntry
{
    public function __construct(
        protected FindsDetour $findContract,
        protected StoresDetour $storeContract,
        protected DeletesDetour $deleteContract,
        protected GetsOldEntryUri $getOldEntryUriContract,
    ) {}

    public function create(Entry $entry): void
    {
        if (! config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        $parentEntryId = $entry->id();
        $parentOldSlug = $entry->getOriginal('slug');
        $parentNewSlug = $entry->slug();

        foreach (EntryHelper::entryAndDescendantIds($entry) as $entryId) {
            /** @var Entry $target */
            $target = $entryId === $parentEntryId
                ? $entry
                : EntryFacade::find($entryId);

            if ($entryId === $parentEntryId) {
                $this->createDetour($target);

                continue;
            }

            $this->createDetour($target, $parentOldSlug, $parentNewSlug);
        }
    }

    protected function createDetour(Entry $entry, ?string $parentOldSlug = null, ?string $parentNewSlug = null): void
    {
        if (! $entry->uri()) {
            return;
        }
        if ($parentOldSlug && $parentNewSlug) {
            $oldUri = $this->getOldEntryUriContract->get($entry, $parentOldSlug, $parentNewSlug);
        } else {
            $oldUri = $this->getOldEntryUriContract->get($entry);
        }

        if (! $oldUri || $entry->uri() === $oldUri) {
            return;
        }

        if ($conflictingDetour = $this->findContract->firstWhere('from', $entry->uri())) {
            $this->deleteContract->delete($conflictingDetour->id);
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
        app()->singleton(CreatesDetoursFromEntry::class, static::class);
    }
}
