<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonasiRequest;
use App\Models\Donasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DonasiController extends Controller
{
    /**
     * Daftar donasi beserta total yang sudah diterima sesuai filter.
     * Filter: search (nama donatur), program, status, dari/sampai (tanggal donasi).
     */
    public function index(Request $request): View
    {
        $query = Donasi::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where('nama_donatur', 'like', "%{$search}%"))
            ->when($request->enum('program', ProgramDonasi::class), fn ($q, $program) => $q->where('program', $program))
            ->when($request->enum('status', StatusDonasi::class), fn ($q, $status) => $q->where('status', $status))
            ->when($request->date('dari'), fn ($q, $dari) => $q->whereDate('tanggal_donasi', '>=', $dari))
            ->when($request->date('sampai'), fn ($q, $sampai) => $q->whereDate('tanggal_donasi', '<=', $sampai));

        return view('admin.donasi.index', [
            'donasi' => (clone $query)->latest('tanggal_donasi')->latest('id')->paginate(10)->withQueryString(),
            'totalDiterima' => (int) (clone $query)->diterima()->sum('nominal'),
            'jumlahMenunggu' => (clone $query)->where('status', StatusDonasi::Menunggu)->count(),
        ]);
    }

    /**
     * Form catat donasi.
     */
    public function create(): View
    {
        return view('admin.donasi.form', ['donasi' => new Donasi(['tanggal_donasi' => today()])]);
    }

    /**
     * Simpan donasi baru.
     */
    public function store(DonasiRequest $request): RedirectResponse
    {
        Donasi::create($request->validated());

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil dicatat.');
    }

    /**
     * Form ubah donasi.
     */
    public function edit(Donasi $donasi): View
    {
        return view('admin.donasi.form', ['donasi' => $donasi]);
    }

    /**
     * Simpan perubahan donasi.
     */
    public function update(DonasiRequest $request, Donasi $donasi): RedirectResponse
    {
        $donasi->update($request->validated());

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil diperbarui.');
    }

    /**
     * Ubah status donasi langsung dari daftar (terima/tolak).
     */
    public function updateStatus(Request $request, Donasi $donasi): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusDonasi::class)],
        ]);

        $donasi->update($validated);

        return back()->with('status', "Donasi {$donasi->nama_donatur} ditandai {$donasi->status->value}.");
    }

    /**
     * Hapus catatan donasi.
     */
    public function destroy(Donasi $donasi): RedirectResponse
    {
        $donasi->delete();

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil dihapus.');
    }
}
