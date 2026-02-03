<?php

namespace JustBetter\Detour\Tests\ServiceProvider;

use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.mode', 'performance');
    }

    #[Test]
    public function it_can_set_performance_mode(): void
    {
        $this->assertTrue(config()->get('justbetter.statamic-detour.mode') === 'performance');
    }
}
