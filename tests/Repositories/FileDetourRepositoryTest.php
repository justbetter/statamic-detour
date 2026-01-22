<?php

namespace JustBetter\Detour\Tests\Repositories;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FileDetourRepositoryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'file');
        $app['config']->set('justbetter.statamic-detour.path', __DIR__.'/../__fixtures__/detours');
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (scandir(__DIR__.'/../__fixtures__/detours') as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }

            unlink(__DIR__.'/../__fixtures__/detours/'.$file);
        }
    }

    #[Test]
    public function it_can_be_queried(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $repository->store($data);

        $all = $repository->get();

        $this->assertCount(1, $all);
    }

    #[Test]
    public function it_can_paginate(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data1 = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $data2 = Form::make([
            'from' => '::from-2::',
            'to' => '::to-2::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $data3 = Form::make([
            'from' => '::from-3::',
            'to' => '::to-3::',
            'code' => '302',
            'type' => '::path::',
        ]);

        $repository->store($data1);
        $repository->store($data2);
        $repository->store($data3);

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
