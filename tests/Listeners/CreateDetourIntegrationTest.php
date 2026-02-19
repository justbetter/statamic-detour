<?php

namespace JustBetter\Detour\Tests\Listeners;

use JustBetter\Detour\Contracts\CachesOldEntryUri;
use JustBetter\Detour\Listeners\CreateDetour;
use JustBetter\Detour\Models\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class CreateDetourIntegrationTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
        $app['config']->set('justbetter.statamic-detour.auto_create', true);
    }

    #[Test]
    public function it_creates_a_detour_after_caching_old_uri_and_saving_a_new_slug(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        // @phpstan-ignore-next-line
        EntryFacade::make()
            ->id('parent')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => 'Parent Entry'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('parent');

        app(CachesOldEntryUri::class)->cache($entry);

        $entry->slug('new');
        $entry->saveQuietly();

        /** @var Entry $fresh */
        $fresh = EntryFacade::find('parent');

        app(CreateDetour::class)->handle(new EntrySaved($fresh));

        $this->assertCount(1, Detour::all());

        $detour = Detour::query()->firstOrFail();
        $this->assertSame('/old', $detour->from);
        $this->assertSame('/new', $detour->to);
        $this->assertSame('path', $detour->type->value);
        $this->assertSame('301', $detour->code);
    }
}
