<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RedirectIfNeededFileTest extends TestCase
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
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }

            unlink(__DIR__.'/../../__fixtures__/detours/'.$file);
        }
    }

    #[Test]
    public function it_redirects_when_a_path_detour_matches(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '::from::',
            'to' => '::to::',
            'code' => '301',
            'type' => 'path',
        ]);

        $repository->store($data);

        $this->get('/::from::')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }

    #[Test]
    public function it_redirects_when_the_from_pattern_is_a_matching_regex(): void
    {
        $contract = app(ResolveRepository::class);
        $repository = $contract->resolve();

        $data = Form::make([
            'from' => '#^/from#',
            'to' => '/::to::',
            'type' => 'regex',
            'code' => '301',
            'sites' => [],
        ]);

        $repository->store($data);

        $this->get('/from/123')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }
}
