<?php

namespace JustBetter\Detour\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Models\Detour;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Blink;
use Statamic\Facades\Entry as EntryFacade;

class CreateRedirect
{
    protected StoresDetour $contract;

    public function __construct(StoresDetour $contract)
    {
        $this->contract = $contract;
    }

    public function handle(EntrySaved|CollectionTreeSaved $event): void
    {
        if (!config()->boolean('justbetter.statamic-detour.auto_create')) {
            return;
        }

        match (true) {
            $event instanceof EntrySaved => $this->createRedirect($event->entry),
            $event instanceof CollectionTreeSaved => $this->createRedirect($this->treeToEntries($event->tree->tree())),
        };
    }

    protected function treeToEntries(array $tree): array
    {
        $ids = [];

        foreach ($tree as $item) {
            $ids = array_merge($ids, $this->gatherEntryIds($item));
        }

        foreach ($ids as $id) {
            Blink::forget('eloquent-entry-' . $id);
        }

        return EntryFacade::query()->whereIn('id', $ids)->get()->all();
    }

    protected function gatherEntryIds(array $item): array
    {
        $ids = [];

        if (isset($item['entry'])) {
            $ids[] = $item['entry'];
        }

        if (!isset($item['children'])) {
            return $ids;
        }

        foreach ($item['children'] as $child) {
            $ids = array_merge($ids, $this->gatherEntryIds($child));
        }

        return $ids;
    }

    //TODO: How can I solve the problem when the slug of a parent is changed? Should that change all its children as well
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

            Detour::query()
                ->where('from', $entry->uri())
                ->delete();


            if (! $oldUri = Cache::pull("redirect-entry-uri-before:{$entry->id()}")) {
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

            $this->contract->store($data);
        }

    }
}