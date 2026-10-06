<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Login panel admin berbasis sesi.
 *
 * Halaman login hanya bisa dibuka lewat /login dan tidak ditautkan dari
 * halaman mana pun.
 */
class LoginController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function create(): View
    {
        return view('admin.login');
    }

    /**
     * Proses login lalu arahkan ke dashboard admin.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        Auth::login($request->autentikasi(), $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Keluar dari panel admin.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}
