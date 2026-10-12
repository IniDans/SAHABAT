<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Pengganti scheduler untuk hosting tanpa cron server (mis. Vercel Cron).
 * Vercel mengirim header "Authorization: Bearer <CRON_SECRET>".
 */
class CronController extends Controller
{
    /**
     * Hapus token API kedaluwarsa dan token reset password lama.
     */
    public function __invoke(Request $request): Response
    {
        $secret = config('services.cron.secret');

        abort_unless($secret && hash_equals('Bearer '.$secret, (string) $request->header('Authorization')), 404);

        Artisan::call('sanctum:prune-expired', ['--hours' => 24]);
        Artisan::call('auth:clear-resets');

        return response()->noContent();
    }
}
