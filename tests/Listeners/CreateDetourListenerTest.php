<?php

namespace JustBetter\Detour\Tests\Listeners;

use Illuminate\Support\Facades\Cache;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Listeners\CreateDetour;
use JustBetter\Detour\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Entry as EntryFacade;

class CreateDetourListenerTest extends TestCase
{
    #[Test]
    public function it_does_nothing_when_auto_create_is_disabled(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', false);

        $tree = Mockery::mock();
        [$listener, $store, $find, $delete] = $this->makeListener();
        $this->expectNoDetourInteractions($store, $find, $delete);

        EntryFacade::shouldReceive('find')->never();
        Cache::shouldReceive('pull')->never();

        $listener->handle(new CollectionTreeSaved($tree));
    }

    #[Test]
    public function it_creates_a_detour_on_entry_saved_when_uri_changed_and_old_uri_is_cached(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', '/new', 'pages');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);
        Cache::shouldReceive('pull')->once()->with('redirect-entry-uri-before:parent')->andReturn('/old');

        [$listener, $store, $find, $delete] = $this->makeListener();
        $store->shouldReceive('store')
            ->once()
            ->withArgs(function (Form $form): bool {
                return $form->from === '/old'
                    && $form->to === '/new'
                    && $form->type === 'path'
                    && $form->code == '301';
            })
            ->andReturn(Detour::make(['id' => 'new']));

        $find->shouldReceive('findBy')->once()->with('from', '/new')->andReturn(null);

        $delete->shouldNotReceive('delete');

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_skips_missing_entries_in_entry_saved_flow(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        EntryFacade::shouldReceive('find')->with('parent')->andReturn(null);

        [$listener, $store, $find, $delete] = $this->makeListener();
        $this->expectNoDetourInteractions($store, $find, $delete);

        Cache::shouldReceive('pull')->never();

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_does_not_create_a_detour_when_no_cached_old_uri_exists(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', '/new', 'pages');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);
        Cache::shouldReceive('pull')->once()->with('redirect-entry-uri-before:parent')->andReturn(null);

        [$listener, $store, $find, $delete] = $this->makeListener();
        $store->shouldNotReceive('store');

        $find->shouldReceive('findBy')->once()->with('from', '/new')->andReturn(null);

        $delete->shouldNotReceive('delete');

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_skips_entries_without_uri(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', null, 'pages');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);

        [$listener, $store, $find, $delete] = $this->makeListener();
        $this->expectNoDetourInteractions($store, $find, $delete);

        Cache::shouldReceive('pull')->never();

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_skips_entries_not_in_pages_collection(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', '/new', 'blog');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);

        [$listener, $store, $find, $delete] = $this->makeListener();
        $this->expectNoDetourInteractions($store, $find, $delete);

        Cache::shouldReceive('pull')->never();

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_skips_when_uri_does_not_change_after_cache_pull(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', '/same', 'pages');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);
        Cache::shouldReceive('pull')->once()->with('redirect-entry-uri-before:parent')->andReturn('/same');

        [$listener, $store, $find, $delete] = $this->makeListener();
        $store->shouldNotReceive('store');

        $find->shouldReceive('findBy')->once()->with('from', '/same')->andReturn(null);

        $delete->shouldNotReceive('delete');

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_deletes_conflicting_detour_before_creating_new_one(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $eventEntry = $this->mockEntrySavedEventEntry();

        $resolvedEntry = $this->mockEntry('parent', '/new', 'pages');
        $existing = Detour::make([
            'id' => 'existing-id',
            'from' => '/new',
            'to' => '/somewhere',
            'type' => 'path',
            'code' => 301,
            'sites' => [],
        ]);

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);
        Cache::shouldReceive('pull')->once()->with('redirect-entry-uri-before:parent')->andReturn('/old');

        [$listener, $store, $find, $delete] = $this->makeListener();
        $store->shouldReceive('store')->once()->andReturn(Detour::make(['id' => 'new']));

        $find->shouldReceive('findBy')->once()->with('from', '/new')->andReturn($existing);

        $delete->shouldReceive('delete')->once()->with('existing-id');

        $listener->handle(new EntrySaved($eventEntry));
    }

    #[Test]
    public function it_creates_detours_for_collection_tree_saved_entries(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

        $tree = Mockery::mock();
        $tree->shouldReceive('tree')->andReturn([
            ['entry' => 'parent'],
        ]);

        $resolvedEntry = $this->mockEntry('parent', '/new', 'pages');

        EntryFacade::shouldReceive('find')->with('parent')->andReturn($resolvedEntry);
        Cache::shouldReceive('pull')->once()->with('redirect-entry-uri-before:parent')->andReturn('/old');

        [$listener, $store, $find, $delete] = $this->makeListener();
        $store->shouldReceive('store')->once()->andReturn(Detour::make(['id' => 'new']));

        $find->shouldReceive('findBy')->once()->with('from', '/new')->andReturn(null);

        $delete->shouldNotReceive('delete');

        $listener->handle(new CollectionTreeSaved($tree));
    }

    /**
     * @return array{CreateDetour, StoresDetour&MockInterface, FindsDetour&MockInterface, DeletesDetour&MockInterface}
     */
    private function makeListener(): array
    {
        /** @var StoresDetour&MockInterface $store */
        $store = $this->mock(StoresDetour::class);
        /** @var FindsDetour&MockInterface $find */
        $find = $this->mock(FindsDetour::class);
        /** @var DeletesDetour&MockInterface $delete */
        $delete = $this->mock(DeletesDetour::class);

        return [new CreateDetour($store, $find, $delete), $store, $find, $delete];
    }

    private function expectNoDetourInteractions(
        StoresDetour&MockInterface $store,
        FindsDetour&MockInterface $find,
        DeletesDetour&MockInterface $delete
    ): void {
        $store->shouldNotReceive('store');
        $find->shouldNotReceive('findBy');
        $delete->shouldNotReceive('delete');
    }

    private function mockEntry(string $id, ?string $uri, string $collectionHandle): MockInterface
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn($id);
        $entry->shouldReceive('uri')->andReturn($uri);

        $collection = Mockery::mock();
        $collection->shouldReceive('handle')->andReturn($collectionHandle);
        $entry->shouldReceive('collection')->andReturn($collection);

        return $entry;
    }

    private function mockEntrySavedEventEntry(string $id = 'parent'): MockInterface
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->andReturn($id);
        $entry->shouldReceive('root')->andReturnSelf();
        $entry->shouldReceive('page')->andReturn(null);

        return $entry;
    }
}
