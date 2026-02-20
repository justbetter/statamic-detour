<?php

namespace JustBetter\Detour\Tests\Utils;

use JustBetter\Detour\Tests\TestCase;
use JustBetter\Detour\Utils\EntryHelper;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class EntryHelperTest extends TestCase
{
    #[Test]
    public function it_returns_empty_ids_when_entry_has_no_id(): void
    {
        $entry = $this->mock(Entry::class);
        $entry->shouldReceive('id')->once()->andReturnNull();
        $entry->shouldReceive('page')->once()->andReturnNull();

        $this->assertSame([], EntryHelper::entryAndDescendantIds($entry));
    }

    #[Test]
    public function it_returns_parent_and_child_ids_from_collection_tree(): void
    {
        $collection = Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->structureContents(['root' => false]) // enable structure
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('parent-id')
            ->collection('pages')
            ->slug('parent')
            ->published(true)
            ->data(['title' => 'Parent'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('child-id')
            ->collection('pages')
            ->slug('child')
            ->published(true)
            ->data(['title' => 'Child'])
            ->save();

        $collection->structure()->in('default')->tree([
            [
                'entry' => 'parent-id',
                'children' => [
                    ['entry' => 'child-id'],
                ],
            ],
        ])->save();

        /** @var \Statamic\Entries\Entry $parent */
        $parent = EntryFacade::find('parent-id');

        $this->assertSame(
            ['parent-id', 'child-id'],
            EntryHelper::entryAndDescendantIds($parent)
        );
    }
}
