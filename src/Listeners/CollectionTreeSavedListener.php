<?php

namespace JustBetter\Detour\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Structures\CollectionTree;

class CollectionTreeSavedListener
{
    public function __construct(
        protected CreatesDetoursFromTree $contract,
    ) {}

    public function handle(CollectionTreeSaved $event): void
    {
        /** @var CollectionTree $tree */
        $tree = $event->tree;

        $this->contract->create($tree);
    }
}
