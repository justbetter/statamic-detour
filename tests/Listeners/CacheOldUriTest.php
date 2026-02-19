<?php

namespace JustBetter\Detour\Tests\Listeners;

use Illuminate\Support\Collection;
use JustBetter\Detour\Contracts\CachesOldEntryUri;
use JustBetter\Detour\Listeners\CacheOldUri;
use JustBetter\Detour\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Structures\CollectionTreeDiff;

class CacheOldUriTest extends TestCase
{
    #[Test]
    public function it_does_nothing_when_auto_create_is_disabled(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', false);

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('parent');

        $contract = $this->makeCacheContract();
        $contract->shouldNotReceive('cache');

        EntryFacade::shouldReceive('find')->never();

        $this->makeListener($contract)->handle(new EntrySaving($entry));
    }

    #[Test]
    public function it_does_not_cache_on_entry_saving_when_entry_has_no_id(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('id')->andReturn(null);

        $contract = $this->makeCacheContract();
        $contract->shouldNotReceive('cache');

        EntryFacade::shouldReceive('find')->never();

        $this->makeListener($contract)->handle(new EntrySaving($entry));
    }

    #[Test]
    public function it_does_not_cache_on_entry_saving_when_slug_is_not_dirty(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn('parent');
        $entry->shouldReceive('isDirty')->with('slug')->andReturnFalse();

        $contract = $this->makeCacheContract();
        $contract->shouldNotReceive('cache');

        EntryFacade::shouldReceive('find')->never();

        $this->makeListener($contract)->handle(new EntrySaving($entry));
    }

    #[Test]
    public function it_caches_parent_and_children_on_entry_saving(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $page = Mockery::mock();
        $page->shouldReceive('flattenedPages')
            ->andReturn(new Collection([
                ['id' => 'child-a'],
                ['id' => 'child-b'],
            ]));

        $eventEntry = $this->mock(Entry::class);
        $eventEntry->shouldReceive('id')->andReturn('parent');
        $eventEntry->shouldReceive('isDirty')->with('slug')->andReturnTrue();
        $eventEntry->shouldReceive('getOriginal')->with('slug')->andReturn('old-parent');
        $eventEntry->shouldReceive('slug')->andReturn('new-parent');
        $eventEntry->shouldReceive('page')->andReturn($page);

        $parentEntry = $this->mock(Entry::class);
        $parentEntry->shouldReceive('id')->andReturn('parent');

        $childAEntry = $this->mock(Entry::class);
        $childAEntry->shouldReceive('id')->andReturn('child-a');

        $childBEntry = $this->mock(Entry::class);
        $childBEntry->shouldReceive('id')->andReturn('child-b');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($parentEntry);
        EntryFacade::shouldReceive('find')->with('child-a')->andReturn($childAEntry);
        EntryFacade::shouldReceive('find')->with('child-b')->andReturn($childBEntry);

        $contract = $this->makeCacheContract();
        $contract->shouldReceive('cache')->once()->with($parentEntry);
        $contract->shouldReceive('cache')->once()->with($childAEntry, 'old-parent', 'new-parent');
        $contract->shouldReceive('cache')->once()->with($childBEntry, 'old-parent', 'new-parent');

        $this->makeListener($contract)->handle(new EntrySaving($eventEntry));
    }

    #[Test]
    public function it_skips_missing_entries_on_entry_saving(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $page = Mockery::mock();
        $page->shouldReceive('flattenedPages')
            ->andReturn(new Collection([
                ['id' => 'child-a'],
            ]));

        $eventEntry = $this->mock(Entry::class);
        $eventEntry->shouldReceive('id')->andReturn('parent');
        $eventEntry->shouldReceive('isDirty')->with('slug')->andReturnTrue();
        $eventEntry->shouldReceive('getOriginal')->with('slug')->andReturn('old-parent');
        $eventEntry->shouldReceive('slug')->andReturn('new-parent');
        $eventEntry->shouldReceive('page')->andReturn($page);

        EntryFacade::shouldReceive('find')->with('parent')->andReturn(null);
        EntryFacade::shouldReceive('find')->with('child-a')->andReturn(null);

        $contract = $this->makeCacheContract();
        $contract->shouldNotReceive('cache');

        $this->makeListener($contract)->handle(new EntrySaving($eventEntry));
    }

    #[Test]
    public function it_caches_affected_entries_and_children_on_collection_tree_saving(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $page = Mockery::mock();
        $page->shouldReceive('flattenedPages')
            ->andReturn(new Collection([
                ['id' => 'child'],
            ]));

        $parentEntry = $this->mock(Entry::class);
        $parentEntry->shouldReceive('id')->andReturn('parent');
        $parentEntry->shouldReceive('getOriginal')->with('slug')->andReturn('old-parent');
        $parentEntry->shouldReceive('slug')->andReturn('new-parent');
        $parentEntry->shouldReceive('page')->andReturn($page);

        $childEntry = $this->mock(Entry::class);
        $childEntry->shouldReceive('id')->andReturn('child');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($parentEntry);
        EntryFacade::shouldReceive('find')->with('parent')->andReturn($parentEntry);
        EntryFacade::shouldReceive('find')->with('child')->andReturn($childEntry);

        $diff = $this->mock(CollectionTreeDiff::class);
        $diff->shouldReceive('affected')->andReturn(['parent']);

        $tree = Mockery::mock();
        $tree->shouldReceive('diff')->andReturn($diff);

        $contract = $this->makeCacheContract();
        $contract->shouldReceive('cache')->once()->with($parentEntry);
        $contract->shouldReceive('cache')->once()->with($childEntry, 'old-parent', 'new-parent');

        $this->makeListener($contract)->handle(new CollectionTreeSaving($tree));
    }

    #[Test]
    public function it_skips_missing_affected_entries_on_collection_tree_saving(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        EntryFacade::shouldReceive('find')->with('missing-parent')->andReturn(null);

        $diff = $this->mock(CollectionTreeDiff::class);
        $diff->shouldReceive('affected')->andReturn(['missing-parent']);

        $tree = Mockery::mock();
        $tree->shouldReceive('diff')->andReturn($diff);

        $contract = $this->makeCacheContract();
        $contract->shouldNotReceive('cache');

        $this->makeListener($contract)->handle(new CollectionTreeSaving($tree));
    }

    #[Test]
    public function it_skips_missing_children_on_collection_tree_saving(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $page = Mockery::mock();
        $page->shouldReceive('flattenedPages')
            ->andReturn(new Collection([
                ['id' => 'missing-child'],
            ]));

        $parentEntry = $this->mock(Entry::class);
        $parentEntry->shouldReceive('id')->andReturn('parent');
        $parentEntry->shouldReceive('getOriginal')->with('slug')->andReturn('old-parent');
        $parentEntry->shouldReceive('slug')->andReturn('new-parent');
        $parentEntry->shouldReceive('page')->andReturn($page);

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($parentEntry);
        EntryFacade::shouldReceive('find')->with('parent')->andReturn($parentEntry);
        EntryFacade::shouldReceive('find')->with('missing-child')->andReturn(null);

        $diff = $this->mock(CollectionTreeDiff::class);
        $diff->shouldReceive('affected')->andReturn(['parent']);

        $tree = Mockery::mock();
        $tree->shouldReceive('diff')->andReturn($diff);

        $contract = $this->makeCacheContract();
        $contract->shouldReceive('cache')->once()->with($parentEntry);

        $this->makeListener($contract)->handle(new CollectionTreeSaving($tree));
    }

    /** @return CachesOldEntryUri&MockInterface */
    private function makeCacheContract(): CachesOldEntryUri
    {
        /** @var CachesOldEntryUri&MockInterface $contract */
        $contract = $this->mock(CachesOldEntryUri::class);

        return $contract;
    }

    private function makeListener(CachesOldEntryUri $contract): CacheOldUri
    {
        return new CacheOldUri($contract);
    }
}
