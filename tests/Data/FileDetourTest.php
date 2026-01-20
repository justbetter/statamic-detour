<?php

namespace JustBetter\Detour\Tests\Data\File;

use Illuminate\Support\Str;
use JustBetter\Detour\Data\FileDetour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FileDetourTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'file');
    }

    #[Test]
    public function it_can_have_data(): void
    {
        $id = Str::uuid()->toString();
        $detour = FileDetour::make([
            'id' => $id,
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $this->assertSame($detour->getAttributes(), [
            'id' => $id,
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }
}
