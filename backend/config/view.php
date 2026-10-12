<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi tampilan Blade
    |--------------------------------------------------------------------------
    |
    | Semua tampilan ada di folder frontend/views di root repo. Di Vercel
    | folder frontend tidak ikut terunggah bersama backend, jadi saat build
    | isinya disalin ke resources/views (lihat vercel.json).
    |
    */

    'paths' => [
        realpath(base_path('../frontend/views')) ?: resource_path('views'),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
