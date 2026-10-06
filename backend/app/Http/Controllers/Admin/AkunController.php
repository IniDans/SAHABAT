<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AkunRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Kelola akun panel admin (khusus admin).
 */
class AkunController extends Controller
{
    /**
     * Daftar akun. Filter: search (nama/email), role, status (aktif/nonaktif).
     */
    public function index(Request $request): View
    {
        $akun = User::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"),
            ))
            ->when($request->enum('role', Role::class), fn ($q, $role) => $q->where('role', $role))
            ->when($request->string('status')->value(), fn ($q, $status) => $q->where('is_active', $status === 'aktif'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.akun.index', ['akun' => $akun]);
    }

    public function create(): View
    {
        return view('admin.akun.form', ['akun' => new User]);
    }

    public function store(AkunRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return to_route('admin.akun.index')->with('status', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $akun): View
    {
        return view('admin.akun.form', ['akun' => $akun]);
    }

    /**
     * Simpan perubahan akun. Admin tidak bisa menurunkan role atau menonaktifkan akunnya sendiri.
     */
    public function update(AkunRequest $request, User $akun): RedirectResponse
    {
        $data = $request->validated();

        if ($akun->is($request->user())) {
            if ($data['role'] !== Role::Admin->value) {
                throw ValidationException::withMessages(['role' => 'Anda tidak bisa menurunkan role akun sendiri.']);
            }

            if (! $data['is_active']) {
                throw ValidationException::withMessages(['is_active' => 'Anda tidak bisa menonaktifkan akun sendiri.']);
            }
        }

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $akun->update($data);

        // Password diganti admin atau akun dinonaktifkan: semua perangkat akun itu harus login ulang.
        if ($akun->wasChanged('password') || ! $akun->is_active) {
            $akun->cabutSemuaAkses();
        }

        return to_route('admin.akun.index')->with('status', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $akun): RedirectResponse
    {
        if ($akun->is($request->user())) {
            return back()->withErrors(['akun' => 'Anda tidak bisa menghapus akun sendiri.']);
        }

        $akun->tokens()->delete();
        $akun->delete();

        return to_route('admin.akun.index')->with('status', "Akun {$akun->name} berhasil dihapus.");
    }
}
