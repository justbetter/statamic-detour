<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\CreatesDetoursFromEntry;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Site as SiteFacade;
use Statamic\Structures\CollectionTree;

class CreateAutoDetourMultisiteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
        $app['config']->set('statamic.system.multisite', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        SiteFacade::setSites([
            'en' => ['name' => 'English', 'url' => '/', 'locale' => 'en_US'],
            'de' => ['name' => 'German', 'url' => '/de/', 'locale' => 'de_DE'],
            'nl' => ['name' => 'Dutch', 'url' => 'https://example.nl/', 'locale' => 'nl_NL'],
        ]);
    }

    /** @param  array<int, string>  $sites */
    protected function storeDetour(string $from, string $to, array $sites): Detour
    {
        return app(ResolvesRepository::class)->resolve()->store(Form::make([
            'from' => $from,
            'to' => $to,
            'code' => 301,
            'type' => Type::Path->value,
            'sites' => $sites,
            'query_string_handling' => 'use_global',
        ]));
    }

    protected function findDetour(string $from): ?Detour
    {
        return app(ResolvesRepository::class)->resolve()->firstWhere('from', $from);
    }

    #[Test]
    public function it_prefixes_paths_for_sites_in_a_subdirectory(): void
    {
        app(CreatesAutoDetour::class)->create('/ueber-uns', '/firma/ueber-uns', 'de');

        $detour = $this->findDetour('/de/ueber-uns');

        $this->assertNotNull($detour);
        $this->assertSame('/de/firma/ueber-uns', $detour->to);
        $this->assertSame(['de'], $detour->sites);
        $this->assertTrue($detour->matches('de', '/de/ueber-uns'));
        $this->assertFalse($detour->matches('en', '/ueber-uns'));
    }

    #[Test]
    public function it_scopes_detours_to_sites_on_a_separate_domain(): void
    {
        app(CreatesAutoDetour::class)->create('/over-ons', '/bedrijf/over-ons', 'nl');

        $detour = $this->findDetour('/over-ons');

        $this->assertNotNull($detour);
        $this->assertSame('/bedrijf/over-ons', $detour->to);
        $this->assertSame(['nl'], $detour->sites);
        $this->assertTrue($detour->matches('nl', '/over-ons'));
        $this->assertFalse($detour->matches('en', '/over-ons'));
    }

    #[Test]
    public function it_only_repoints_detours_of_the_same_site(): void
    {
        $sameSite = $this->storeDetour('/first', '/about', ['nl']);
        $otherSite = $this->storeDetour('/first', '/about', ['en']);
        $shared = $this->storeDetour('/second', '/about', ['en', 'nl']);
        $global = $this->storeDetour('/third', '/about', []);

        app(CreatesAutoDetour::class)->create('/about', '/company/about', 'nl');

        $repository = app(ResolvesRepository::class)->resolve();

        $this->assertSame('/company/about', $repository->find($sameSite->id)?->to);
        $this->assertSame('/about', $repository->find($otherSite->id)?->to);
        $this->assertSame('/about', $repository->find($shared->id)?->to);
        $this->assertSame('/about', $repository->find($global->id)?->to);
    }

    #[Test]
    public function it_only_deletes_conflicting_detours_of_the_same_site(): void
    {
        $sameSite = $this->storeDetour('/company/about', '/elsewhere', ['nl']);
        $otherSite = $this->storeDetour('/company/about', '/elsewhere', ['en']);

        app(CreatesAutoDetour::class)->create('/about', '/company/about', 'nl');

        $this->assertDatabaseMissing('detours', ['id' => $sameSite->id]);
        $this->assertDatabaseHas('detours', ['id' => $otherSite->id]);
    }

    #[Test]
    public function it_creates_site_specific_detours_when_a_localized_slug_changes(): void
    {
        CollectionFacade::make('pages')
            ->sites(['en', 'de'])
            ->routes(['en' => '/{slug}', 'de' => '/{slug}'])
            ->save();

        /** @var Entry $entry */
        $entry = EntryFacade::make();

        $entry->id('page')
            ->locale('de')
            ->collection('pages')
            ->slug('alt')
            ->published(true)
            ->saveQuietly();

        /** @var Entry $entry */
        $entry = EntryFacade::find('page');
        $entry->slug('neu');

        /** @var Blink $blinkCache */
        $blinkCache = Blink::store('entry-uris');
        $blinkCache->forget('page');

        app(CreatesDetoursFromEntry::class)->create($entry);

        $detour = $this->findDetour('/de/alt');

        $this->assertNotNull($detour);
        $this->assertSame('/de/neu', $detour->to);
        $this->assertSame(['de'], $detour->sites);
    }

    #[Test]
    public function it_creates_site_specific_detours_when_restructuring_a_localized_tree(): void
    {
        /** @var Collection $collection */
        $collection = CollectionFacade::make('pages')
            ->sites(['en', 'de'])
            ->routes(['en' => '{parent_uri}/{slug}', 'de' => '{parent_uri}/{slug}'])
            ->structureContents(['root' => false])
            ->save();

        foreach (['a' => 'a', 'b' => 'b', 'child' => 'kind'] as $id => $slug) {
            /** @var Entry $entry */
            $entry = EntryFacade::make();

            $entry->id($id)
                ->locale('de')
                ->collection('pages')
                ->slug($slug)
                ->published(true)
                ->saveQuietly();
        }

        /** @var CollectionTree $tree */
        $tree = $collection->structure()->in('de');
        $tree->tree([
            ['entry' => 'a', 'children' => [['entry' => 'child']]],
            ['entry' => 'b'],
        ])->save();

        $tree->tree([
            ['entry' => 'a'],
            ['entry' => 'b', 'children' => [['entry' => 'child']]],
        ])->save();

        $detour = $this->findDetour('/de/a/kind');

        $this->assertNotNull($detour);
        $this->assertSame('/de/b/kind', $detour->to);
        $this->assertSame(['de'], $detour->sites);
    }
}
