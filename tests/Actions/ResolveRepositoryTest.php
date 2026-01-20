<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Exceptions\DriverNotFound;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResolveRepositoryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', '::nonsense::');
    }

    #[Test]
    public function it_resolves_a_repository(): void
    {
        $repository = app(ResolvesRepository::class);
        $this->expectException(DriverNotFound::class);

        $repository->resolve();
    }
}
