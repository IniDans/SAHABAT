<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Riwayat tambah, ubah, hapus, dan login pengguna panel admin. Khusus admin.
 */
class LogAktivitasController extends Controller
{
    /**
     * Filter: search (nama data), user, jenis, aksi, dari/sampai (Y-m-d).
     */
    public function index(Request $request): View
    {
        $log = LogAktivitas::query()
            ->with('user')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('subjek_label', 'like', "%{$search}%"))
            ->when($request->integer('user'), fn ($q, $user) => $q->where('user_id', $user))
            ->when($request->string('jenis')->value(), fn ($q, $jenis) => $q->where('subjek_tipe', $jenis))
            ->when($request->string('aksi')->value(), fn ($q, $aksi) => $q->where('aksi', $aksi))
            ->when($request->date('dari'), fn ($q, $dari) => $q->where('created_at', '>=', $dari->startOfDay()))
            ->when($request->date('sampai'), fn ($q, $sampai) => $q->where('created_at', '<=', $sampai->endOfDay()))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.log-aktivitas.index', [
            'log' => $log,
            'pengguna' => User::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
