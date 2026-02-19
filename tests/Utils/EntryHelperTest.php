<?php

namespace JustBetter\Detour\Tests\Utils;

use JustBetter\Detour\Tests\TestCase;
use JustBetter\Detour\Utils\EntryHelper;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class EntryHelperTest extends TestCase
{
    #[Test]
    public function it_returns_empty_ids_when_entry_has_no_id(): void
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('id')->andReturn(null);

        $ids = EntryHelper::entryAndDescendantIds($entry);

        $this->assertSame([], $ids);
    }

    #[Test]
    public function it_skips_non_entry_results_when_mapping_tree_to_entries(): void
    {
        $tree = [
            ['entry' => 'missing'],
            ['entry' => 'valid'],
        ];

        $validEntry = Mockery::mock(Entry::class);

        EntryFacade::shouldReceive('find')->with('missing')->andReturn(null);
        EntryFacade::shouldReceive('find')->with('valid')->andReturn($validEntry);

        $entries = EntryHelper::treeToEntries($tree);

        $this->assertCount(1, $entries);
        $this->assertSame([$validEntry], $entries);
    }

    #[Test]
    public function it_skips_non_array_and_non_string_key_children_when_gathering_entry_ids(): void
    {
        $item = [
            'entry' => 'parent',
            'children' => [
                'not-an-array-child',
                [0 => 'invalid-numeric-key'],
                ['entry' => 'child-valid'],
            ],
        ];

        $ids = EntryHelper::gatherEntryIds($item);

        $this->assertSame(['parent', 'child-valid'], $ids);
    }

    #[Test]
    public function it_recursively_gathers_entry_ids_from_valid_children(): void
    {
        $item = [
            'entry' => 'parent',
            'children' => [
                [
                    'entry' => 'child-a',
                    'children' => [
                        ['entry' => 'grandchild-a1'],
                    ],
                ],
                [
                    'entry' => 'child-b',
                ],
            ],
        ];

        $ids = EntryHelper::gatherEntryIds($item);

        $this->assertSame(['parent', 'child-a', 'grandchild-a1', 'child-b'], $ids);
    }
}
