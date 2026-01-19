<?php

namespace JustBetter\Detour\Tests\Data\File;

use JustBetter\Detour\Data\File\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'file');
    }

    #[Test]
    public function it_can_be_made(): void
    {
        $detour = Detour::make();

        $this->assertNotNull($detour->id());
    }

    #[Test]
    public function it_can_have_data(): void
    {
        $detour = Detour::make();

        $detour->data([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $this->assertSame($detour->data(), [
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }

    #[Test]
    public function it_can_be_a_json_response(): void
    {
        $detour = Detour::make();

        $detour->data([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $this->assertSame($detour->jsonSerialize(), [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }
}
