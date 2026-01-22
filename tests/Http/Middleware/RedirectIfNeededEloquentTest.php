<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Data\Form;
use JustBetter\Detour\Models\Detour;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RedirectIfNeededEloquentTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
    }

    protected function defineRoutes($router)
    {
        $router->middleware('web')->get('/::from::', fn () => '::from::');
        $router->middleware('web')->get('/::to::', fn () => '::to::');
        $router->middleware('web')->get('/::other::', fn () => '::other::');
    }

    #[Test]
    public function it_redirects_when_a_path_detour_matches(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'code' => '301',
            'type' => 'path',
        ]);

        $this->get('/::from::')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }

    #[Test]
    public function it_redirects_when_from_path_is_missing_a_leading_slash(): void
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
    public function it_does_not_redirect_when_no_detour_exists(): void
    {
        $response = $this->get('/::from::');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_does_not_redirect_when_detour_exists_but_not_for_current_site(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => ['::site::'],
        ]);

        $this->get('/::from::')
            ->assertSee('::from::')
            ->assertStatus(200);
    }

    #[Test]
    public function it_does_not_redirect_when_detour_exists_but_not_for_requested_route(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/::other::')
            ->assertSee('::other::')
            ->assertStatus(200);
    }

    #[Test]
    public function it_redirects_when_the_from_pattern_is_a_matching_regex(): void
    {
        Detour::create([
            'from' => '#^/from#',
            'to' => '/::to::',
            'type' => 'regex',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/from/123')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }

    #[Test]
    public function it_does_not_redirect_when_the_regex_is_invalid(): void
    {
        Detour::create([
            'from' => '[a-z',
            'to' => '/::to::',
            'type' => 'regex',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/::from::')
            ->assertSee('::from::')
            ->assertStatus(200);
    }
}
