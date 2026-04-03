<?php

use JustBetter\Detour\Repositories\EloquentRepository;
use JustBetter\Detour\Repositories\FileRepository;

return [

    'driver' => env('STATAMIC_DETOUR_DRIVER', 'file'),

    'drivers' => [
        'file' => FileRepository::class,
        'eloquent' => EloquentRepository::class,
    ],

    'path' => base_path('content/detours'),

    'settings_path' => base_path('content/detours/settings.yaml'),

    'query_string_default_handling' => env('STATAMIC_DETOUR_QUERY_STRING_DEFAULT_HANDLING', 'strip_completely'),

    'query_string_default_strip_keys' => env('STATAMIC_DETOUR_QUERY_STRING_DEFAULT_STRIP_KEYS', ''),

    'mode' => env('STATAMIC_DETOUR_MODE', 'basic'), // basic | performance

    'auto_create' => env('STATAMIC_DETOUR_AUTO_CREATE', true),

    'actions' => [
        'disk' => 'local',
    ],

    'queue' => 'default',

    'permissions' => [
        'access' => 'access detours',
    ],
];
