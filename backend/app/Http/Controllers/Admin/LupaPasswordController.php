<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as AturanPassword;
use Illuminate\View\View;

/**
 * Reset password lewat tautan email. Akun nonaktif tidak bisa me-reset password.
 */
class LupaPasswordController extends Controller
{
    public function create(): View
    {
        return view('admin.lupa-password');
    }

    /**
     * Kirim tautan reset. Pesannya selalu sama agar tidak membocorkan email mana yang terdaftar.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        Password::sendResetLink([
            'email' => $request->string('email')->value(),
            'is_active' => true,
        ]);

        return back()->with('status', 'Jika email tersebut terdaftar, tautan reset password sudah kami kirim. Periksa kotak masuk atau folder spam.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('admin.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Simpan password baru, lalu keluarkan akun dari semua perangkat lain.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'max:255', AturanPassword::defaults()],
        ]);

        $status = Password::reset(
            [...$request->only('email', 'password', 'password_confirmation', 'token'), 'is_active' => true],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();
                $user->cabutSemuaAkses();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        return to_route('login')->with('status', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
