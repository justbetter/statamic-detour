<?php

namespace JustBetter\Detour\Tests\Repositories;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Data\Paginate;
use JustBetter\Detour\Enums\Type;
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
            'type' => Type::Path,
        ]);

        DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $all = $repository->get();

        $this->assertCount(2, $all);
    }

    #[Test]
    public function it_can_be_queried_by_find_function(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $createdDetour = DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        /** @var Detour $foundDetour */
        $foundDetour = $repository->find($createdDetour->id);

        $this->assertSame($createdDetour->id, $foundDetour->id);
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
            'type' => Type::Path,
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
            'type' => Type::Path,
        ]);
    }

    #[Test]
    public function it_can_paginate(): void
    {
        DetourModel::create([
            'from' => '::from-1::',
            'to' => '::to-1::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        DetourModel::create([
            'from' => '::from-2::',
            'to' => '::to-2::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        DetourModel::create([
            'from' => '::from-3::',
            'to' => '::to-3::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $paginate = Paginate::make([
            'size' => 2,
            'page' => 1,
        ]);

        $results = $repository->paginate($paginate);

        $this->assertSame(2, $results->count());

        $paginate = Paginate::make([
            'size' => 2,
            'page' => 2,
        ]);

        $results2 = $repository->paginate($paginate);

        $this->assertSame(1, $results2->count());
    }

    #[Test]
    public function it_can_be_updated(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $model = DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $data = Form::make([
            'from' => '::from-new::',
            'to' => '::to-new::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $repository->update($model->id, $data);

        $this->assertDatabaseHas('detours', [
            'id' => $model->id,
            'from' => '::from-new::',
            'to' => '::to-new::',
            'code' => '302',
            'type' => Type::Path,
        ]);
    }

    #[Test]
    public function it_will_create_model_if_not_updateable(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '::from-new::',
            'to' => '::to-new::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $repository->update('nonsense', $data);

        $this->assertDatabaseHas('detours', [
            'from' => '::from-new::',
            'to' => '::to-new::',
            'code' => '302',
            'type' => Type::Path,
        ]);
    }
}
