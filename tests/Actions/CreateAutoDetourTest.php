<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\CreatesAutoDetour;
use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class CreateAutoDetourTest extends TestCase
{
    protected function storeDetour(string $from, string $to, Type $type = Type::Path): Detour
    {
        return app(ResolvesRepository::class)->resolve()->store(Form::make([
            'from' => $from,
            'to' => $to,
            'code' => 301,
            'type' => $type->value,
            'query_string_handling' => 'use_global',
        ]));
    }

    /** @return array<int, Detour> */
    protected function detours(): array
    {
        return app(ResolvesRepository::class)->resolve()->get()
            ->sortBy('from')
            ->values()
            ->all();
    }

    /** @return array<string, array{string}> */
    public static function drivers(): array
    {
        return [
            'file' => ['file'],
            'eloquent' => ['eloquent'],
        ];
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_creates_a_detour(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        app(CreatesAutoDetour::class)->create('/old', '/new', 'default');

        $detours = $this->detours();

        $this->assertCount(1, $detours);
        $this->assertSame('/old', $detours[0]->from);
        $this->assertSame('/new', $detours[0]->to);
        $this->assertSame(Type::Path, $detours[0]->type());
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_does_nothing_when_from_equals_to(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        app(CreatesAutoDetour::class)->create('/same', '/same', 'default');

        $this->assertCount(0, $this->detours());
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_repoints_existing_chains_to_the_new_uri(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        $existing = $this->storeDetour('/first', '/old');

        app(CreatesAutoDetour::class)->create('/old', '/new', 'default');

        $detours = $this->detours();

        $this->assertCount(2, $detours);
        $this->assertSame($existing->id, $detours[0]->id);
        $this->assertSame('/first', $detours[0]->from);
        $this->assertSame('/new', $detours[0]->to);
        $this->assertSame('/old', $detours[1]->from);
        $this->assertSame('/new', $detours[1]->to);
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_deletes_chains_that_would_become_a_loop(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        $this->storeDetour('/new', '/old');

        app(CreatesAutoDetour::class)->create('/old', '/new', 'default');

        $detours = $this->detours();

        $this->assertCount(1, $detours);
        $this->assertSame('/old', $detours[0]->from);
        $this->assertSame('/new', $detours[0]->to);
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_replaces_existing_detours_from_the_old_uri(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        $this->storeDetour('/old', '/elsewhere');

        app(CreatesAutoDetour::class)->create('/old', '/new', 'default');

        $detours = $this->detours();

        $this->assertCount(1, $detours);
        $this->assertSame('/new', $detours[0]->to);
    }

    #[Test]
    #[DataProvider('drivers')]
    public function it_leaves_regex_detours_untouched(string $driver): void
    {
        config()->set('justbetter.statamic-detour.driver', $driver);

        $this->storeDetour('#^/new$#', '/old', Type::Regex);

        app(CreatesAutoDetour::class)->create('/old', '/new', 'default');

        $detours = $this->detours();

        $this->assertCount(2, $detours);
        $this->assertSame('#^/new$#', $detours[0]->from);
        $this->assertSame('/old', $detours[0]->to);
    }
}
