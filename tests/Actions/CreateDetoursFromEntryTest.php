<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Actions\CreateDetoursFromEntry;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Contracts\DeletesDetour;
use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Contracts\GetsOldEntryUri;
use JustBetter\Detour\Contracts\StoresDetour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry as EntryFacade;

class CreateDetoursFromEntryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_does_not_create_when_disabled(): void
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

        /** @var Entry $entry */
        $entry = EntryFacade::make();

        $entry->id('::id::')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::');
        $entry->slug('new');

        $action = app(CreateDetoursFromEntry::class);
        $action->create($entry);

        $this->assertDatabaseCount('detours', 0);
    }

    #[Test]
    public function it_can_create_detours_from_entries(): void
    {
        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::make();

        $entry->id('::id::')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->saveQuietly();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::');
        $entry->slug('new');

        /** @var Blink $blinkCache */
        $blinkCache = Blink::store('entry-uris');
        $blinkCache->forget($entry->id());

        $this->assertSame('old', $entry->getOriginal('slug'));
        $this->assertSame('new', $entry->slug());

        $action = app(CreateDetoursFromEntry::class);
        $action->create($entry);

        $this->assertDatabaseCount('detours', 1);
    }

    #[Test]
    public function it_can_delete_conflicting_detours(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '/new',
            'to' => '/old',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $detour = $repository->store($data);

        Collection::make('pages')
            ->routes(['default' => '/{slug}'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::make();

        $entry->id('::id::')
            ->collection('pages')
            ->slug('old')
            ->published(true)
            ->data(['title' => '::title::'])
            ->saveQuietly();

        /** @var Entry $entry */
        $entry = EntryFacade::find('::id::');
        $entry->slug('new');

        /** @var Blink $blinkCache */
        $blinkCache = Blink::store('entry-uris');
        $blinkCache->forget($entry->id());

        $this->assertSame('old', $entry->getOriginal('slug'));
        $this->assertSame('new', $entry->slug());

        $action = app(CreateDetoursFromEntry::class);
        $action->create($entry);

        $this->assertDatabaseCount('detours', 1);
        $this->assertDatabaseMissing('detours', ['id' => $detour->id]);
    }
}
