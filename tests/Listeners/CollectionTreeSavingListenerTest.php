<?php

namespace JustBetter\Detour\Tests\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use JustBetter\Detour\Listeners\CollectionTreeSavingListener;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Structures\CollectionTree;

class CollectionTreeSavingListenerTest extends TestCase
{
    #[Test]
    public function it_prepares_detours_from_the_tree_being_saved(): void
    {
        /** @var Collection $collection */
        $collection = CollectionFacade::make('pages')
            ->routes(['default' => '{parent_uri}/{slug}'])
            ->structureContents(['root' => false])
            ->save();

        /** @var CollectionTree $tree */
        $tree = $collection->structure()->in('default');

        $this->mock(CreatesDetoursFromTree::class, function (MockInterface $mock) use ($tree): void {
            $mock->shouldReceive('prepare')->once()->with($tree);
        });

        app(CollectionTreeSavingListener::class)->handle(new CollectionTreeSaving($tree));
    }
}
