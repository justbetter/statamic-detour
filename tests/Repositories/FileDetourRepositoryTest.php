<?php

namespace JustBetter\Detour\Tests\Repositories;

use Illuminate\Support\Str;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Detour;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Data\Paginate;
use JustBetter\Detour\Enums\Type;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FileDetourRepositoryTest extends TestCase
{
    #[Test]
    public function it_can_be_queried(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $repository->store($data);

        $all = $repository->get();

        $this->assertCount(1, $all);
    }

    #[Test]
    public function it_can_find(): void
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

        $found = $repository->find($detour->id);

        $this->assertInstanceOf(Detour::class, $found);
    }

    #[Test]
    public function it_returns_null_if_not_found(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $nullValue = $repository->find(Str::random());

        $this->assertNull($nullValue);
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

        $repository->delete($detour->id);

        $this->assertNull($repository->find($detour->id));
    }

    #[Test]
    public function it_can_paginate(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $detourOne = Form::make([
            'from' => '::from-1::',
            'to' => '::to-1::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $detourTwo = Form::make([
            'from' => '::from-2::',
            'to' => '::to-2::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $detourThree = Form::make([
            'from' => '::from-3::',
            'to' => '::to-3::',
            'code' => '302',
            'type' => Type::Path,
        ]);

        $repository->store($detourOne);
        $repository->store($detourTwo);
        $repository->store($detourThree);

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
}
