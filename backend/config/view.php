<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi tampilan Blade
    |--------------------------------------------------------------------------
    |
    | Semua tampilan ada di folder frontend/views di root repo.
    |
    */

    'paths' => [
        base_path('../frontend/views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
