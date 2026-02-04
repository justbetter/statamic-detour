<?php

namespace JustBetter\Detour\Jobs;

use JustBetter\Detour\Contracts\ImportsDetours;
use JustBetter\Detour\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ImportDetoursTest extends TestCase
{
    #[Test]
    public function it_can_call_action(): void
    {
        $file = 'test.csv';
        $this->mock(ImportsDetours::class, function (MockInterface $mock) use ($file) {
            $mock->shouldReceive('import')->once()->with($file);
        });

        ImportDetours::dispatch($file);
    }
}
