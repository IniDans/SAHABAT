<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPesan;
use App\Http\Controllers\Controller;
use App\Models\Pesan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PesanController extends Controller
{
    /**
     * Daftar pesan dari formulir Kontak. Filter: search (nama/email/subjek), status, bulan (Y-m), urut.
     * Bawaannya semua bulan, supaya pesan lama yang belum dibaca tidak tersembunyi.
     */
    public function index(Request $request): View
    {
        $bulan = $this->bulanDipilih($request);
        $arah = $request->string('urut')->value() === 'terlama' ? 'asc' : 'desc';

        $pesan = Pesan::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subjek', 'like', "%{$search}%")))
            ->when($request->enum('status', StatusPesan::class), fn ($q, $status) => $q->where('status', $status))
            ->when($bulan, fn ($q) => $this->dalamBulan($q, $bulan))
            ->orderBy('created_at', $arah)
            ->orderBy('id', $arah)
            ->paginate(10)
            ->withQueryString();

        $jumlahPerStatus = Pesan::query()
            ->when($bulan, fn ($q) => $this->dalamBulan($q, $bulan))
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->map(fn ($jumlah): int => (int) $jumlah);

        return view('admin.pesan.index', [
            'pesan' => $pesan,
            'bulan' => $bulan,
            'pilihanBulan' => $this->pilihanBulan(),
            'jumlahTotal' => $jumlahPerStatus->sum(),
            'jumlahPerStatus' => $jumlahPerStatus,
            'adaBelumDibaca' => Pesan::where('status', StatusPesan::BelumDibaca)->exists(),
        ]);
    }

    /**
     * Baca satu pesan. Pesan yang belum dibaca otomatis ditandai dibaca.
     */
    public function show(Pesan $pesan): View
    {
        if ($pesan->status === StatusPesan::BelumDibaca) {
            $pesan->update(['status' => StatusPesan::Dibaca]);
        }

        return view('admin.pesan.show', ['pesan' => $pesan]);
    }

    /**
     * Ubah status pesan, mis. arsipkan atau tandai belum dibaca.
     */
    public function updateStatus(Request $request, Pesan $pesan): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusPesan::class)],
        ]);

        $pesan->update($validated);
        $pesanStatus = "Pesan dari {$pesan->nama} ditandai {$pesan->status->label()}.";

        // Kembali ke halaman detail akan menandainya dibaca lagi, jadi kembali ke daftar.
        if ($pesan->status === StatusPesan::BelumDibaca) {
            return to_route('admin.pesan.index')->with('status', $pesanStatus);
        }

        return back()->with('status', $pesanStatus);
    }

    /**
     * Tandai pesan sudah dibalas lalu buka aplikasi email admin untuk menulis balasan.
     * Aplikasi ini tidak mengirim email sendiri, jadi balasan ditulis lewat mailto:.
     */
    public function balas(Pesan $pesan): RedirectResponse
    {
        $pesan->update(['status' => StatusPesan::Dibalas]);

        return redirect()->away($pesan->mailtoBalasan());
    }

    /**
     * Tandai semua pesan yang belum dibaca sebagai dibaca.
     */
    public function tandaiSemuaDibaca(): RedirectResponse
    {
        $jumlah = Pesan::where('status', StatusPesan::BelumDibaca)->update(['status' => StatusPesan::Dibaca]);

        return back()->with('status', $jumlah ? "{$jumlah} pesan ditandai sudah dibaca." : 'Tidak ada pesan yang belum dibaca.');
    }

    /**
     * Hapus pesan.
     */
    public function destroy(Pesan $pesan): RedirectResponse
    {
        $pesan->delete();

        return to_route('admin.pesan.index')->with('status', 'Pesan berhasil dihapus.');
    }

    /**
     * @param  Builder<Pesan>  $query
     */
    private function dalamBulan(Builder $query, Carbon $bulan): void
    {
        $query->whereBetween('created_at', [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()]);
    }
}
