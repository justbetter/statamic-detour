<?php

namespace JustBetter\Detour\Tests\Actions;

use Illuminate\Support\Facades\Event;
use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\CreatesDetoursFromTree;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Events\CollectionTreeEntriesMovedOrRemoved;
use Statamic\Events\CollectionTreeSaving;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Structures\CollectionTree;

class CreateDetoursFromTreeTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    /**
     * @param  array<string, string>  $entries  id => slug
     * @param  array<int, array<string, mixed>>  $tree
     */
    protected function setUpPages(array $entries, array $tree, bool $root = false): CollectionTree
    {
        /** @var Collection $collection */
        $collection = CollectionFacade::make('pages')
            ->routes(['default' => '{parent_uri}/{slug}'])
            ->structureContents(['root' => $root])
            ->save();

        foreach ($entries as $id => $slug) {
            /** @var Entry $entry */
            $entry = EntryFacade::make();

            $entry->id($id)
                ->collection('pages')
                ->slug($slug)
                ->published(true)
                ->data(['title' => $slug])
                ->saveQuietly();
        }

        /** @var CollectionTree $collectionTree */
        $collectionTree = $collection->structure()->in('default');
        $collectionTree->tree($tree)->save();

        $this->assertDatabaseCount('detours', 0);

        return $collectionTree;
    }

    #[Test]
    public function it_creates_detours_when_a_page_moves_to_another_parent(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertDatabaseCount('detours', 1);
        $this->assertDatabaseHas('detours', ['from' => '/a/child', 'to' => '/b/child', 'code' => 301]);
    }

    #[Test]
    public function it_creates_detours_for_descendants_of_a_moved_page(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child', 'grandchild' => 'grandchild'],
            [
                ['entry' => 'a', 'children' => [
                    ['entry' => 'child', 'children' => [['entry' => 'grandchild']]],
                ]],
                ['entry' => 'b'],
            ],
        );

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [
                ['entry' => 'child', 'children' => [['entry' => 'grandchild']]],
            ]],
        ])->save();

        $this->assertDatabaseCount('detours', 2);
        $this->assertDatabaseHas('detours', ['from' => '/a/child', 'to' => '/b/child']);
        $this->assertDatabaseHas('detours', ['from' => '/a/child/grandchild', 'to' => '/b/child/grandchild']);
    }

    #[Test]
    public function it_creates_detours_when_a_page_moves_to_the_top_level(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
            ],
        );

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'child'],
        ])->save();

        $this->assertDatabaseHas('detours', ['from' => '/a/child', 'to' => '/child']);
    }

    #[Test]
    public function it_does_not_create_detours_when_reordering_within_the_same_parent(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b'],
            [['entry' => 'a'], ['entry' => 'b']],
        );

        $tree->tree([['entry' => 'b'], ['entry' => 'a']])->save();

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_skips_unpublished_entries(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        /** @var Entry $child */
        $child = EntryFacade::find('child');
        $child->published(false)->saveQuietly();

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_does_not_create_detours_when_disabled(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', false);

        $this->mock(CreatesAutoDetour::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('create');
        });

        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_does_not_create_detours_for_collections_without_a_route(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        /** @var Collection $collection */
        $collection = CollectionFacade::findByHandle('pages');
        $collection->routes([])->save();

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_does_not_create_detours_for_orderable_collections(): void
    {
        /** @var Collection $collection */
        $collection = CollectionFacade::make('pages')
            ->routes(['default' => '/{slug}'])
            ->structureContents(['max_depth' => 1])
            ->save();

        foreach (['a', 'b'] as $id) {
            /** @var Entry $entry */
            $entry = EntryFacade::make();
            $entry->id($id)->collection('pages')->slug($id)->published(true)->saveQuietly();
        }

        /** @var CollectionTree $tree */
        $tree = $collection->structure()->in('default');
        $tree->tree([['entry' => 'a'], ['entry' => 'b']])->save();
        $tree->tree([['entry' => 'b'], ['entry' => 'a']])->save();

        $this->assertTrue($collection->orderable());
        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_resolves_new_uris_after_moving(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        /** @var Entry $child */
        $child = EntryFacade::find('child');

        $this->assertSame('/b/child', $child->uri());
    }

    #[Test]
    public function it_keeps_statamics_moved_entries_diff_intact(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        $moved = [];

        Event::listen(CollectionTreeEntriesMovedOrRemoved::class, function (CollectionTreeEntriesMovedOrRemoved $event) use (&$moved): void {
            $moved = $event->moved;
        });

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertSame(['child'], $moved);
    }

    #[Test]
    public function it_does_not_create_detours_when_the_save_is_cancelled(): void
    {
        $tree = $this->setUpPages(
            ['a' => 'a', 'b' => 'b', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
                ['entry' => 'b'],
            ],
        );

        Event::listen(CollectionTreeSaving::class, fn (): bool => false);

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_does_nothing_when_create_is_called_without_prepare(): void
    {
        $tree = $this->setUpPages(['a' => 'a'], [['entry' => 'a']]);

        $this->mock(CreatesAutoDetour::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('create');
        });

        app(CreatesDetoursFromTree::class)->create($tree);
    }

    #[Test]
    public function it_creates_detours_for_children_moved_up_when_the_parent_is_deleted(): void
    {
        $this->setUpPages(
            ['a' => 'a', 'child' => 'child'],
            [
                ['entry' => 'a', 'children' => [['entry' => 'child']]],
            ],
        );

        /** @var Entry $parent */
        $parent = EntryFacade::find('a');
        $parent->delete();

        $this->assertDatabaseHas('detours', ['from' => '/a/child', 'to' => '/child']);
    }
}
