<?php

namespace JustBetter\Detour;

use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Repositories\File\DetourRepository;
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

    public function bootAddon(): void
    {
        $this->bootConfig()
            ->bootRepository()
            ->bootNavigation();
    }

    public function register(): void
    {
        parent::register();

        $this->registerConfig();
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

    protected function bootRepository(): static
    {
        $driver = config('justbetter.statamic-detour.driver');

        if ($driver === 'file') {
            /** @var string $path */
            $path = config('justbetter.statamic-detour.path');
            $this->app->bind(DetourRepositoryContract::class, fn () => new DetourRepository($path));
        }

        return $this;
    }

    protected function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-detour.php', 'justbetter.statamic-detour');

        return $this;
    }

    protected function bootConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/statamic-detour.php' => config_path('static-cache-warmer.php'),
        ], 'justbetter-statamic-detour');

        return $this;
    }
}
