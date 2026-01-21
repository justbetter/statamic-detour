<?php

use JustBetter\Detour\Repositories\EloquentRepository;
use JustBetter\Detour\Repositories\FileRepository;

return [

    'driver' => 'file',

    'drivers' => [
        'file' => FileRepository::class,
        'eloquent' => EloquentRepository::class,
    ],

    'path' => base_path('content/detours'),

    'mode' => env('STATAMIC_DETOUR_MODE', 'basic'), // basic | performance
];
