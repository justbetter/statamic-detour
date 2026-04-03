<?php

namespace JustBetter\Detour\Tests\Http\Middleware;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Enums\QueryStringHandling;
use JustBetter\Detour\Models\Detour;
use JustBetter\Detour\Support\DetourSettings;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RedirectIfNeededEloquentTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.driver', 'eloquent');
        $app['config']->set('justbetter.statamic-detour.settings_path', __DIR__.'/../../__fixtures__/settings.yaml');
    }

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(__DIR__.'/../../__fixtures__/settings.yaml');
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
    public function it_does_not_redirect_when_path_type_detour_exists_but_not_for_requested_route(): void
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
    public function it_does_not_redirect_when_regex_type_detour_exists_but_not_for_requested_route(): void
    {
        Detour::create([
            'from' => '#/::regex::#',
            'to' => '/::to::',
            'type' => 'regex',
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
    public function it_redirects_when_the_from_pattern_is_a_complex_matching_regex(): void
    {
        Detour::create([
            'from' => '#^/blog/(\d{4})/(\d{2})/(.*)$#',
            'to' => '/articles/$1/$2/$3',
            'type' => 'regex',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/blog/2024/01/hello-world')
            ->assertRedirect('/articles/2024/01/hello-world')
            ->assertStatus(301);
    }

    #[Test]
    public function it_passes_through_query_parameters_when_enabled(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
            'query_string_handling' => 'pass_through',
        ]);

        $this->get('/::from::?utm_source=test&gclid=123')
            ->assertRedirect('/::to::?utm_source=test&gclid=123')
            ->assertStatus(301);
    }

    #[Test]
    public function it_strips_query_parameters_by_default(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/::from::?utm_source=test&gclid=123')
            ->assertRedirect('/::to::')
            ->assertStatus(301);
    }

    #[Test]
    public function it_strips_specific_query_keys_when_configured(): void
    {
        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
            'query_string_handling' => 'strip_specific_keys',
            'query_string_strip_keys' => 'gclid, fbclid',
        ]);

        $this->get('/::from::?utm_source=test&gclid=123&fbclid=234')
            ->assertRedirect('/::to::?utm_source=test')
            ->assertStatus(301);
    }

    #[Test]
    public function it_uses_the_global_default_query_string_handling_when_detour_has_no_override(): void
    {
        app(DetourSettings::class)->update(
            QueryStringHandling::PassThrough,
            ''
        );

        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/::from::?utm_source=test&gclid=123')
            ->assertRedirect('/::to::?utm_source=test&gclid=123')
            ->assertStatus(301);
    }

    #[Test]
    public function it_uses_global_strip_keys_when_detour_uses_global_mode(): void
    {
        app(DetourSettings::class)->update(
            QueryStringHandling::StripSpecificKeys,
            'gclid,fbclid'
        );

        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
        ]);

        $this->get('/::from::?utm_source=test&gclid=123&fbclid=234')
            ->assertRedirect('/::to::?utm_source=test')
            ->assertStatus(301);
    }

    #[Test]
    public function it_uses_global_default_when_detour_explicitly_uses_global(): void
    {
        app(DetourSettings::class)->update(
            QueryStringHandling::PassThrough,
            ''
        );

        Detour::create([
            'from' => '/::from::',
            'to' => '/::to::',
            'type' => 'path',
            'code' => '301',
            'sites' => [],
            'query_string_handling' => 'use_global',
        ]);

        $this->get('/::from::?utm_source=test&gclid=123')
            ->assertRedirect('/::to::?utm_source=test&gclid=123')
            ->assertStatus(301);
    }
}
