<?php

namespace JustBetter\Detour\Tests\Repositories\Eloquent;

use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\Eloquent\Detour;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourRepositoryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_can_be_queried(): void
    {
        $detour = Detour::make();

        DetourModel::create([
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $detour = Detour::make();

        DetourModel::create([
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $repository = app(DetourRepositoryContract::class);

        $all = $repository->all();

        $this->assertCount(2, $all);
    }

    #[Test]
    public function it_can_be_created(): void
    {
        $repository = app(DetourRepositoryContract::class);

        $detour = Detour::make();

        $data = [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour->data($data);

        $repository->save($detour);

        $this->assertDatabaseHas('detours', [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }

    #[Test]
    public function it_can_be_deleted(): void
    {
        $repository = app(DetourRepositoryContract::class);

        $detour = Detour::make();

        $data = [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour->data($data);

        $repository->save($detour);

        $repository->delete($detour);

        $this->assertDatabaseMissing('detours', [
            'id' => $detour->id(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }
}
