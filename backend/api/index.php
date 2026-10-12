<?php

/*
|--------------------------------------------------------------------------
| Entry point Vercel
|--------------------------------------------------------------------------
|
| Vercel menjalankan PHP sebagai serverless function dari folder api/.
| Semua request diteruskan ke public/index.php milik Laravel. Di Vercel
| hanya /tmp yang bisa ditulisi, jadi storage dan cache bootstrap
| Laravel dipindah ke sana. Nilai yang diisi di dashboard Vercel tetap
| diutamakan.
|
*/

$storage = '/tmp/storage';

foreach (['app', 'fonts', 'framework/cache/data', 'framework/views', 'logs'] as $folder) {
    if (! is_dir("{$storage}/{$folder}")) {
        // @: request lain di instance yang sama bisa membuat folder lebih dulu.
        @mkdir("{$storage}/{$folder}", 0755, true);
    }
}

// Laravel hanya membaca LARAVEL_STORAGE_PATH dari $_SERVER/$_ENV, bukan getenv().
$_SERVER['LARAVEL_STORAGE_PATH'] = $storage;

$bawaan = [
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'LOG_CHANNEL' => 'stderr',
    'TRUSTED_PROXIES' => '*',
];

foreach ($bawaan as $nama => $nilai) {
    if (getenv($nama) === false && ! isset($_SERVER[$nama])) {
        $_SERVER[$nama] = $nilai;
    }
}

// Tanpa ini Laravel menganggap /api sebagai base URL dan route /api/... tidak ditemukan.
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../public/index.php';
