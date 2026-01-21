<?php

namespace JustBetter\Detour;

use Illuminate\Routing\Router;
use JustBetter\Detour\Actions\DeleteDetour;
use JustBetter\Detour\Actions\ListDetours;
use JustBetter\Detour\Actions\ResolveRepository;
use JustBetter\Detour\Actions\StoreDetour;
use JustBetter\Detour\Http\Middleware\RedirectIfNeeded;
use JustBetter\Detour\Repositories\FileRepository;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    /** @phpstan-ignore-next-line */
    protected $vite = [
        'input' => [
            'resources/css/cp.css',
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register(): void
    {
        parent::register();

        $this
            ->registerConfig()
            ->registerRepository()
            ->registerMiddleware();
    }

    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-detour.php', 'justbetter.statamic-detour');

        return $this;
    }

    protected function registerRepository(): static
    {
        ResolveRepository::bind();
        DeleteDetour::bind();
        ListDetours::bind();
        StoreDetour::bind();

        FileRepository::bind();

        return $this;
    }

    protected function registerMiddleware(): static
    {
        $this->app->booted(function () {
            $router = app(Router::class);
            if (config('justbetter.statamic-detour.mode') === 'performance') {
                $router->pushMiddlewareToGroup('web', RedirectIfNeeded::class);
            } else {
                $router->prependMiddlewareToGroup('web', RedirectIfNeeded::class);
            }
        });

        return $this;
    }

    public function bootAddon(): void
    {
        $this
            ->bootConfig()
            ->bootNavigation()
            ->bootMigrations();
    }

    protected function bootConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/statamic-detour.php' => config_path('statamic-detour.php'),
        ], 'justbetter-statamic-detour');

        return $this;
    }

    protected function bootNavigation(): static
    {
        Nav::extend(
            function ($nav) {
                $nav->content('Detours')
                    ->section('Tools')
                    ->route('justbetter.detours.index')
                    ->icon('<svg class="svg-icon" style="width: 2em; height: 2em;vertical-align: middle;text-align:center;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M653.328 125.024l-56.576 56.704L734.88 320H399.68C240.88 320 112 448.992 112 607.776c0 158.816 128 287.952 288 287.952v-80c-112 0-208-93.312-208-208.016 0-114.688 93.152-208 207.84-208h334.96l-137.888 137.856 56.528 56.56 234.48-234.496L653.344 125.024z" fill="#565D64" /></svg>');
            });

        return $this;
    }

    protected function bootMigrations(): static
    {
        $driver = config('justbetter.statamic-detour.driver');

        if ($driver !== 'eloquent') {
            return $this;
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }
}
