<?php

namespace JustBetter\Detour\Tests\Data\Eloquent;

use JustBetter\Detour\Data\Eloquent\Detour;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_can_be_made(): void
    {
        $detour = Detour::make();

        $this->assertNotNull($detour->id());
    }

    #[Test]
    public function it_can_have_a_model(): void
    {
        $detour = Detour::make();

        $model = DetourModel::create([
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $detour->model($model);
        $this->assertSame($detour->model(), $model);
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
