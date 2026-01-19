<?php

return [

    'driver' => 'file', // file | eloquent

    'path' => base_path('content/detours'),

    'mode' => env('STATAMIC_DETOUR_MODE', 'basic'), // basic | performance
];
