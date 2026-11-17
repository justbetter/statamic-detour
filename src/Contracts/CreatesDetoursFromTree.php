<?php

namespace JustBetter\Detour\Contracts;

use Statamic\Structures\CollectionTree;

interface CreatesDetoursFromTree
{
    public function prepare(CollectionTree $tree): void;

    public function create(CollectionTree $tree): void;
}
