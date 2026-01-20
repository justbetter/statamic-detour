<?php

namespace JustBetter\Detour\Tests\Data\Eloquent;

use Illuminate\Support\Str;
use JustBetter\Detour\Data\EloquentDetour;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EloquentDetourTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_can_have_a_model(): void
    {
        $model = DetourModel::create([
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $detour = EloquentDetour::fromModel($model);

        $this->assertSame($detour->model(), $model);
    }

    #[Test]
    public function it_can_have_data(): void
    {
        $detour = EloquentDetour::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $this->assertSame($detour->getAttributes(), [
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }
}
