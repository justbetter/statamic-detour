<?php

namespace JustBetter\Detour\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Structures\CollectionTree;

class CollectionTreeSavingListener
{
    public function __construct(
        protected CreatesDetoursFromTree $contract,
    ) {}

    public function handle(CollectionTreeSaving $event): void
    {
        /** @var CollectionTree $tree */
        $tree = $event->tree;

        $this->contract->prepare($tree);
    }
}
