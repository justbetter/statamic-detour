<?php

namespace JustBetter\Detour\Tests\Repositories\Eloquent;

use Illuminate\Support\Str;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\EloquentDetour;
use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EloquentDetourRepositoryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    #[Test]
    public function it_can_be_queried(): void
    {
        $model = DetourModel::create([
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        EloquentDetour::fromModel($model);

        $model = DetourModel::create([
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        EloquentDetour::fromModel($model);

        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $all = $repository->all();

        $this->assertCount(2, $all);
    }

    #[Test]
    public function it_can_be_created(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = [
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour = EloquentDetour::make($data);

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
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = [
            'id' => Str::uuid()->toString(),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour = EloquentDetour::make($data);
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
