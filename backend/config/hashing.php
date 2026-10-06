<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Password di-hash dengan Argon2id. Setiap hash otomatis memakai salt acak
    | 16 byte yang disimpan di dalam string hash itu sendiri, jadi tidak perlu
    | kolom salt terpisah. Dua password yang sama tetap menghasilkan hash yang
    | berbeda.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'argon2id'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    */

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => env('HASH_VERIFY', true),
        'limit' => env('BCRYPT_LIMIT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Memory dalam KiB (64 MiB), time = jumlah iterasi. Nilai bawaan ini
    | mengikuti rekomendasi OWASP untuk Argon2id.
    |
    | verify dimatikan supaya hash bcrypt lama di database masih bisa dicek.
    | Hash lama itu langsung diganti ke Argon2id saat pemiliknya berhasil login
    | (lihat App\Http\Requests\Concerns\MemeriksaKredensial).
    |
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => env('HASH_VERIFY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rehash On Login
    |--------------------------------------------------------------------------
    */

    'rehash_on_login' => true,

];
