<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\GetsOldEntryUri;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class GetOldEntryUriTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_entry_has_no_uri(): void
    {
        Collection::make('pages')
            ->save();

        // @phpstan-ignore-next-line
        $entry = EntryFacade::make()
            ->id('::id::no-uri::')
            ->collection('pages')
            ->slug('new')
            ->published(true)
            ->data(['title' => '::title::']);

        $action = app(GetsOldEntryUri::class);

        $this->assertNull($action->get($entry));
    }

    #[Test]
    public function it_returns_null_when_entry_is_unpublished(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('::id::unpublished::')
            ->collection('pages')
            ->slug('old')
            ->published(false)
            ->data(['title' => '::title::'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::unpublished::');
        $entry->slug('new');

        $action = app(GetsOldEntryUri::class);

        $this->assertNull($action->get($entry));
    }

    #[Test]
    public function it_uses_original_slug_when_present(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('::id::original::')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::original::');
        $entry->slug('new');

        $action = app(GetsOldEntryUri::class);

        $this->assertSame('/old', $action->get($entry));
    }

    #[Test]
    public function it_falls_back_to_current_slug_when_original_slug_is_missing(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        $entry = EntryFacade::make()
            ->id('::id::fallback::')
            ->collection('pages')
            ->slug('new')
            ->published(true)
            ->data(['title' => '::title::']);

        $action = app(GetsOldEntryUri::class);

        $this->assertSame('/new', $action->get($entry));
    }

    #[Test]
    public function it_replaces_parent_slug_when_both_parent_slugs_are_provided(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/new-parent/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('::id::parent::')
            ->collection('pages')
            ->slug('child-old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::parent::');
        $entry->slug('child-new');

        $action = app(GetsOldEntryUri::class);

        $this->assertSame('/old-parent/child-old', $action->get($entry, 'old-parent', 'new-parent'));
    }
}
