<?php

namespace JustBetter\Detour\Tests\Repositories\File;

use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Data\Eloquent\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetourRepositoryTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'file');
        $app['config']->set('justbetter.statamic-detour.path', __DIR__.'/../../__fixtures__/detours');
    }

    protected function setUp(): void
    {
        parent::setUp();

        foreach (scandir(__DIR__.'/../../__fixtures__/detours') as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            unlink(__DIR__.'/../../__fixtures__/detours/'.$file);
        }
    }

    #[Test]
    public function it_can_be_queried(): void
    {
        $repository = app(DetourRepositoryContract::class);
        $detour = Detour::make();
        $repository->save($detour);

        $detour = Detour::make();
        $repository->save($detour);

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

        $path = config('justbetter.statamic-detour.path');

        $this->assertFileExists($path.'/'.$detour->id().'.yaml');
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
        $path = config('justbetter.statamic-detour.path');
        $this->assertFileDoesNotExist($path.'/'.$detour->id().'.yaml');
    }

    #[Test]
    public function it_can_not_find_what_does_not_exist(): void
    {
        $repository = app(DetourRepositoryContract::class);
        $detour = $repository->find('::non-existing::');

        $this->assertNull($detour);
    }
}
