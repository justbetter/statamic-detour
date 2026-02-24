<?php

namespace JustBetter\Detour\Tests\Actions;

use JustBetter\Detour\Contracts\FindsDetour;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FindDetourTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_can_find_a_detour_by_field(): void
    {
        $action = app(FindsDetour::class);

        $createdDetour = DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $result = $action->firstWhere('from', '::from::');

        $this->assertNotNull($result);
        $this->assertEquals($createdDetour->id, $result->id);
        $this->assertEquals('::from::', $result->from);
        $this->assertEquals('::to::', $result->to);
        $this->assertEquals('302', $result->code);
        $this->assertEquals(Type::Path->value, $result->type);
    }
}
