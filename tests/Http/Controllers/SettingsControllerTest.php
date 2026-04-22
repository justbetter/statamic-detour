<?php

namespace JustBetter\Detour\Tests\Http\Controllers;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Enums\QueryStringHandling;
use JustBetter\Detour\Models\DetourSetting;
use JustBetter\Detour\Support\DetourSettings;
use JustBetter\Detour\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SettingsControllerTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('justbetter.statamic-detour.settings_path', __DIR__.'/../../__fixtures__/settings.yaml');
    }

    protected function setUp(): void
    {
        parent::setUp();

        File::delete(__DIR__.'/../../__fixtures__/settings.yaml');
    }

    #[Test]
    public function it_can_load_the_settings_page(): void
    {
        $this->withoutMiddleware()
            ->get(cp_route('justbetter.detours.settings.index'))
            ->assertOk();
    }

    #[Test]
    public function it_can_store_settings(): void
    {
        $this->withoutMiddleware()
            ->post(cp_route('justbetter.detours.settings.update'), [
                'query_string_default_handling' => 'pass_through',
                'query_string_default_strip_keys' => '',
            ])
            ->assertRedirect(cp_route('justbetter.detours.settings.index'));

        $this->assertSame(
            QueryStringHandling::PassThrough,
            app(DetourSettings::class)->defaultQueryStringHandling()
        );
        $this->assertSame('', app(DetourSettings::class)->defaultQueryStringStripKeys());
    }

    #[Test]
    public function it_stores_settings_in_database_for_eloquent_driver(): void
    {
        config()->set('justbetter.statamic-detour.driver', 'eloquent');

        $this->withoutMiddleware()
            ->post(cp_route('justbetter.detours.settings.update'), [
                'query_string_default_handling' => 'strip_specific_keys',
                'query_string_default_strip_keys' => 'gclid,fbclid',
            ])
            ->assertRedirect(cp_route('justbetter.detours.settings.index'));

        $handlingSetting = DetourSetting::where('key', 'query_string_default_handling')->first();
        $keysSetting = DetourSetting::where('key', 'query_string_default_strip_keys')->first();

        $this->assertNotNull($handlingSetting);
        $this->assertSame('strip_specific_keys', $handlingSetting->value);
        $this->assertNotNull($keysSetting);
        $this->assertSame('gclid,fbclid', $keysSetting->value);
    }

    #[Test]
    public function it_requires_strip_keys_when_strip_specific_keys_is_selected(): void
    {
        $this->withoutMiddleware()
            ->post(cp_route('justbetter.detours.settings.update'), [
                'query_string_default_handling' => 'strip_specific_keys',
                'query_string_default_strip_keys' => '',
            ])
            ->assertSessionHasErrors(['query_string_default_strip_keys']);
    }
}
