<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use JustBetter\Detour\Models\Detour as DetourModel;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RedirectIfNeededTest extends TestCase
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
    public function it_can_redirect(): void
    {
        DetourModel::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'code' => '301',
            'type' => '::path::',
        ]);

        $this->get('/::from::')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }

    #[Test]
    public function it_allows_request_when_no_detour_exists(): void
    {
        $response = $this->get('/::from::');
        $response->assertStatus(200);
    }

    #[Test]
    public function it_does_not_redirect_when_detour_exists_but_not_for_current_site(): void
    {
        DetourModel::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => ['other-site'],
        ]);

        $this->get('/::from::')
            ->assertSee('::from::')
            ->assertStatus(200);
    }

    #[Test]
    public function it_allows_request_when_detour_exists_but_not_for_requested_route(): void
    {
        DetourModel::create([
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
    public function it_allows_redirecting_through_a_regex(): void
    {
        DetourModel::create([
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
    public function it_does_not_break_with_a_faulty_regex(): void
    {
        DetourModel::create([
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
