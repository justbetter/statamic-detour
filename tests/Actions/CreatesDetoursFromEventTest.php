<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\CreatesDetoursFromEvent;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\GetsOldEntryUri;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class CreatesDetoursFromEventTest extends TestCase
{
    #[Test]
    public function it_does_not_auto_create_when_disabled(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', false);

        $this->mock(FindsDetour::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('findBy');
        });

        $this->mock(StoresDetour::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('store');
        });

        $this->mock(DeletesDetour::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('delete');
        });

        $this->mock(GetsOldEntryUri::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('get');
        });

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

        $entry = EntryFacade::find('::id::');
        $entry->slug('new');

        $action = app(CreatesDetoursFromEvent::class);
        $action->createFromEntrySaved(new EntrySaved($entry));

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_auto_creates_when_enabled(): void
    {
        config()->set('justbetter.statamic-detour.auto_create', true);

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

        $entry = EntryFacade::find('::id::');
        $entry->slug('new');


    }
}
