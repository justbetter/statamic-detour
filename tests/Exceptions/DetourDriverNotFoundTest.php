<?php

namespace JustBetter\Detour\Tests\Data\File;

use JustBetter\Detour\Data\File\Detour;
use JustBetter\Detour\Exceptions\DetourDriverNotFound;
use JustBetter\Detour\ServiceProvider;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourDriverNotFoundTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        // $app['config']->set('justbetter.statamic-detour.driver', null);
    }

    #[Test]
    public function it_can_throw_exceptions(): void
    {
        // dd(config('justbetter.statamic-detour.driver'), config('statamic-detour.driver'));
        // $this->expectException(DetourDriverNotFound::class);

        // $provider = new ServiceProvider($this->app);
        // $provider->boot();
    }
}
