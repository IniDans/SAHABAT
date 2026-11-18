<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lokasi tampilan Blade
    |--------------------------------------------------------------------------
    |
    | Semua tampilan ada di folder frontend/views di root repo. Di hosting yang
    | hanya memberi satu folder (mis. server capstone DTEI), isi VIEW_PATH
    | dengan path relatif dari folder backend, mis. "resources/views".
    |
    */

    'paths' => [
        base_path(env('VIEW_PATH', '../frontend/views')),
    ],

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
