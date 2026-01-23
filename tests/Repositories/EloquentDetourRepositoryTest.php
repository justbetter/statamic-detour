<?php

namespace JustBetter\Detour\Tests\Repositories\Eloquent;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Form;
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
        DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $all = $repository->get();

        $this->assertCount(2, $all);
    }

    #[Test]
    public function it_can_be_deleted(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $detour = $repository->store($data);
        /** @var string $id */
        $id = $detour->get('id');
        $repository->delete($id);

        $this->assertDatabaseMissing('detours', [
            'id' => $detour->get('$id'),
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);
    }
}
