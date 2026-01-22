<?php

namespace JustBetter\Detour\Tests\Repositories\Eloquent;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Detour;
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
    public function it_can_be_queried_by_find_function(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $createdDetour = DetourModel::create([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
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

    #[Test]
    public function it_can_paginate(): void
    {
        DetourModel::create([
            'from' => '::from-1::',
            'to' => '::to-1::',
            'code' => '302',
            'type' => '::path::',
        ]);

        DetourModel::create([
            'from' => '::from-2::',
            'to' => '::to-2::',
            'code' => '302',
            'type' => '::path::',
        ]);

        DetourModel::create([
            'from' => '::from-3::',
            'to' => '::to-3::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $paginated = $repository->paginate(2, 1);

        $this->assertSame(1, $paginated->currentPage());
        $this->assertSame(2, $paginated->lastPage());
        $this->assertSame(3, $paginated->total());
        $this->assertCount(2, $paginated->items());

        $paginated = $repository->paginate(2, 2);

        $this->assertSame(2, $paginated->currentPage());
        $this->assertSame(2, $paginated->lastPage());
        $this->assertSame(3, $paginated->total());
        $this->assertCount(1, $paginated->items());
    }
}
