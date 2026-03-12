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
    public function it_returns_parent_and_child_ids_from_collection_tree(): void
    {
        $collection = Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->structureContents(['root' => false]) // enable structure
            ->save();

        /** @var Entry $parentEntry */
        $parentEntry = EntryFacade::make();

        $parentEntry->id('parent-id')
            ->collection('pages')
            ->slug('parent')
            ->published(true)
            ->data(['title' => 'Parent'])
            ->save();

        /** @var Entry $childEntry */
        $childEntry = EntryFacade::make();

        $childEntry
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

        /** @var Entry $parent */
        $parent = EntryFacade::find('parent-id');

        $this->assertSame(
            ['parent-id', 'child-id'],
            EntryHelper::entryAndDescendantIds($parent)
        );
    }
}
