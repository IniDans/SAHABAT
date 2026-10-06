<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Ubah nama, email, dan password akun yang sedang login.
 */
class AkunSayaController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.akun-saya', ['akun' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validateWithBag('profil', [
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($user)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
        ], attributes: ['name' => 'nama']));

        return to_route('admin.akun-saya.edit')->with('status', 'Data akun berhasil diperbarui.');
    }

    /**
     * Ganti password, lalu keluarkan akun dari semua perangkat lain. Sesi ini tetap login.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'max:255', 'different:current_password', Password::defaults()],
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ], [
            'current_password' => 'password saat ini',
            'password' => 'password baru',
        ]);

        $user = $request->user();
        $user->update(['password' => $validated['password']]);
        $user->cabutSemuaAkses();

        $request->session()->regenerate();

        return to_route('admin.akun-saya.edit')->with('status', 'Password berhasil diganti. Perangkat lain harus login ulang.');
    }
}
