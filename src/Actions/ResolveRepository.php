<?php

namespace JustBetter\Detour\Actions;

use JustBetter\Detour\Contracts\ResolvesRepository;
use JustBetter\Detour\Exceptions\DriverNotFound;
use JustBetter\Detour\Repositories\BaseRepository;

class ResolveRepository implements ResolvesRepository
{
    public function resolve(): BaseRepository
    {
        $driver = config()->string('justbetter.statamic-detour.driver');

        /** @var array<string, class-string<BaseRepository>> $drivers */
        $drivers = config()->array('justbetter.statamic-detour.drivers', []);

        if (! array_key_exists($driver, $drivers)) {
            throw new DriverNotFound('Invalid Detour driver: '.$driver);
        }

        $class = $drivers[$driver];

        return app($class);
    }

    public static function bind(): void
    {
        app()->singleton(ResolvesRepository::class, static::class);
    }
}
