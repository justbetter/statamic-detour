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

        $all = $repository->all();

        $this->assertCount(1, $all);
    }
}
