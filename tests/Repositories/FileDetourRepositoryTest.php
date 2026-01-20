<?php

namespace JustBetter\Detour\Tests\Repositories;

use Illuminate\Support\Str;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\FileDetour;
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
        $id = Str::uuid()->toString();
        $detour = FileDetour::make(['id' => $id]);
        $repository->save($detour);

        $id = Str::uuid()->toString();
        $detour = FileDetour::make(['id' => $id]);
        $repository->save($detour);
        $all = $repository->all();

        $this->assertCount(2, $all);
    }

    #[Test]
    public function it_can_be_created(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();
        $id = Str::uuid()->toString();
        $data = [
            'id' => $id,
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour = FileDetour::make($data);
        $repository->save($detour);
        $path = config()->string('justbetter.statamic-detour.path');

        $this->assertFileExists($path.'/'.$detour->id().'.yaml');
    }

    #[Test]
    public function it_can_be_deleted(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $id = Str::uuid()->toString();
        $data = [
            'id' => $id,
            'from' => '::from::',
            'to' => '::to::',
            'code' => '302',
            'type' => '::path::',
        ];

        $detour = FileDetour::make($data);
        $repository->save($detour);
        $repository->delete($detour);
        $path = config()->string('justbetter.statamic-detour.path');
        $this->assertFileDoesNotExist($path.'/'.$detour->id().'.yaml');
    }

    #[Test]
    public function it_can_not_find_what_does_not_exist(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $detour = $repository->find('::non-existing::');

        $this->assertNull($detour);
    }
}
