<?php

namespace JustBetter\Detour\Tests\Listeners;

use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use JustBetter\Detour\Listeners\CollectionTreeSavedListener;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Structures\CollectionTree;

class CollectionTreeSavedListenerTest extends TestCase
{
    #[Test]
    public function it_creates_detours_from_the_saved_tree(): void
    {
        /** @var Collection $collection */
        $collection = CollectionFacade::make('pages')
            ->routes(['default' => '{parent_uri}/{slug}'])
            ->structureContents(['root' => false])
            ->save();

        /** @var CollectionTree $tree */
        $tree = $collection->structure()->in('default');

        $this->mock(CreatesDetoursFromTree::class, function (MockInterface $mock) use ($tree): void {
            $mock->shouldReceive('create')->once()->with($tree);
        });

        app(CollectionTreeSavedListener::class)->handle(new CollectionTreeSaved($tree));
    }
}
