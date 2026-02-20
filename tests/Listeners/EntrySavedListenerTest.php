<?php

namespace JustBetter\Detour\Tests\Listeners;

use Illuminate\Support\Facades\Event;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class EntrySavedListenerTest extends TestCase
{
    #[Test]
    public function it_dispatches_entry_saved_when_slug_changes(): void
    {
        Event::fake([EntrySaved::class]);

        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('::id::')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::');

        $entry->slug('new');
        $entry->save();

        Event::assertDispatched(EntrySaved::class, function (EntrySaved $event) use ($entry) {
            return $event->entry->id() === $entry->id();
        });
    }
}
