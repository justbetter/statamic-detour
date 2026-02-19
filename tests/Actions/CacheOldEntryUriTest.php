<?php

namespace JustBetter\Detour\Tests\Actions;

use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use JustBetter\Detour\Actions\CacheOldEntryUri;
use JustBetter\Detour\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;

class CacheOldEntryUriTest extends TestCase
{
    #[Test]
    public function it_caches_old_uri_from_original_slug(): void
    {
        $action = new CacheOldEntryUri;
        $entry = $this->mockEntry('/new', true, 'old', 'new', 'parent');

        Cache::shouldReceive('put')
            ->once()
            ->with(
                'redirect-entry-uri-before:parent',
                '/old',
                Mockery::type(DateTimeInterface::class)
            );

        $action->cache($entry);
    }

    #[Test]
    public function it_falls_back_to_current_slug_when_original_slug_is_missing(): void
    {
        $action = new CacheOldEntryUri;
        $entry = $this->mockEntry('/new', true, null, 'new', 'parent');

        Cache::shouldReceive('put')
            ->once()
            ->with(
                'redirect-entry-uri-before:parent',
                '/new',
                Mockery::type(DateTimeInterface::class)
            );

        $action->cache($entry);
    }

    #[Test]
    public function it_rewrites_descendant_uri_when_parent_slug_is_provided(): void
    {
        $action = new CacheOldEntryUri;
        $entry = $this->mockEntry('/new/child', true, 'child', 'child', 'child-entry');

        Cache::shouldReceive('put')
            ->once()
            ->with(
                'redirect-entry-uri-before:child-entry',
                '/old/child',
                Mockery::type(DateTimeInterface::class)
            );

        $action->cache($entry, 'old', 'new');
    }

    #[Test]
    public function it_does_not_cache_when_entry_has_no_uri_or_is_unpublished(): void
    {
        $action = new CacheOldEntryUri;

        $entryWithoutUri = $this->mockEntry(null, true, 'old', 'new', 'parent');
        $unpublishedEntry = $this->mockEntry('/new', false, 'old', 'new', 'parent');

        Cache::shouldReceive('put')->never();

        $action->cache($entryWithoutUri);
        $action->cache($unpublishedEntry);
    }

    private function mockEntry(
        ?string $uri,
        bool $published,
        ?string $originalSlug,
        string $slug,
        string $id
    ): Entry {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('uri')->andReturn($uri);

        if ($uri !== null) {
            $entry->shouldReceive('published')->andReturn($published);
        }

        if ($uri !== null && $published) {
            $entry->shouldReceive('getOriginal')->with('slug')->andReturn($originalSlug);
            $entry->shouldReceive('slug')->andReturn($slug);
            $entry->shouldReceive('id')->andReturn($id);
        }

        return $entry;
    }
}
