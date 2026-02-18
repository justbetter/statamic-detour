<?php

use JustBetter\Detour\Repositories\EloquentRepository;
use JustBetter\Detour\Repositories\FileRepository;

return [

    'driver' => env('STATAMIC_DETOUR_DRIVER', 'eloquent'),

    'drivers' => [
        'file' => FileRepository::class,
        'eloquent' => EloquentRepository::class,
    ],

    'path' => base_path('content/detours'),

    'mode' => env('STATAMIC_DETOUR_MODE', 'basic'), // basic | performance

    'auto_create' => true,

    'actions' => [
        'disk' => 'local',
    ],

    'queue' => 'default',
];
