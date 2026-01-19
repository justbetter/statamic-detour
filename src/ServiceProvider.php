<?php

namespace JustBetter\Detour;

use JustBetter\Detour\Contracts\DetourRepositoryContract;
use JustBetter\Detour\Repositories\File\DetourRepository;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];


    public function bootAddon(): void
    {
        $this->bootConfig()
            ->bootRepository();
    }

    public function register(): void
    {
        parent::register();

        $this->registerConfig();
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
