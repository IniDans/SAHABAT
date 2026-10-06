<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonasiRequest;
use App\Models\Donasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DonasiController extends Controller
{
    /**
     * Pilihan urutan daftar donasi: kunci query => [label, kolom, arah].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const URUTAN = [
        'terbaru' => ['Terbaru', 'tanggal_donasi', 'desc'],
        'terlama' => ['Terlama', 'tanggal_donasi', 'asc'],
        'terbesar' => ['Nominal terbesar', 'nominal', 'desc'],
        'terkecil' => ['Nominal terkecil', 'nominal', 'asc'],
    ];

    /**
     * Daftar donasi beserta total yang sudah diterima per program pada bulan terpilih.
     * Filter: search (nama/email/WA), program, status, bulan (Y-m atau "semua"), urut.
     */
    public function index(Request $request): View
    {
        $bulan = $this->bulanDipilih($request, now()->format('Y-m'));

        $donasi = $this->filtered($request, $bulan)
            ->tap(fn (Builder $query) => $this->urutkan($query, $request))
            ->paginate(10)
            ->withQueryString();

        $totalPerProgram = Donasi::query()
            ->diterima()
            ->when($bulan, fn ($q) => $this->dalamBulan($q, $bulan))
            ->selectRaw('program, sum(nominal) as total')
            ->groupBy('program')
            ->pluck('total', 'program')
            ->map(fn ($total): int => (int) $total);

        return view('admin.donasi.index', [
            'donasi' => $donasi,
            'bulan' => $bulan,
            'pilihanBulan' => $this->pilihanBulan(),
            'totalDiterima' => $totalPerProgram->sum(),
            'totalPerProgram' => collect(ProgramDonasi::cases())
                ->mapWithKeys(fn (ProgramDonasi $program): array => [$program->value => $totalPerProgram->get($program->value, 0)]),
            'jumlahMenunggu' => Donasi::where('status', StatusDonasi::Menunggu)->count(),
        ]);
    }

    /**
     * Unduh donasi sesuai filter yang sedang dipakai sebagai CSV.
     */
    public function ekspor(Request $request): StreamedResponse
    {
        $bulan = $this->bulanDipilih($request, now()->format('Y-m'));
        $query = $this->filtered($request, $bulan)->tap(fn (Builder $query) => $this->urutkan($query, $request));
        $namaFile = 'donasi-'.($bulan?->format('Y-m') ?? 'semua').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            // BOM agar Excel membaca huruf non-ASCII dengan benar.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Nama donatur', 'Ditampilkan sebagai', 'No WhatsApp', 'Email', 'Alamat', 'Program', 'Nominal', 'Metode', 'Status', 'Keterangan']);

            foreach ($query->lazy() as $item) {
                fputcsv($out, [
                    $item->tanggal_donasi->toDateString(),
                    $item->nama_donatur,
                    $item->tampil_sebagai->value,
                    $item->no_whatsapp,
                    $item->email,
                    $item->alamat,
                    $item->program->value,
                    $item->nominal,
                    $item->metode_pembayaran->value,
                    $item->status->value,
                    $item->keterangan,
                ]);
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

    /**
     * Query donasi dengan semua filter dari request.
     *
     * @return Builder<Donasi>
     */
    private function filtered(Request $request, ?Carbon $bulan): Builder
    {
        return Donasi::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('nama_donatur', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('no_whatsapp', 'like', "%{$search}%")))
            ->when($request->enum('program', ProgramDonasi::class), fn ($q, $program) => $q->where('program', $program))
            ->when($request->enum('status', StatusDonasi::class), fn ($q, $status) => $q->where('status', $status))
            ->when($bulan, fn ($q) => $this->dalamBulan($q, $bulan));
    }

    /**
     * @param  Builder<Donasi>  $query
     */
    private function dalamBulan(Builder $query, Carbon $bulan): void
    {
        $query->whereBetween('tanggal_donasi', [$bulan->copy()->startOfMonth()->toDateString(), $bulan->copy()->endOfMonth()->toDateString()]);
    }

    /**
     * @param  Builder<Donasi>  $query
     */
    private function urutkan(Builder $query, Request $request): void
    {
        [, $kolom, $arah] = self::URUTAN[$request->string('urut')->value()] ?? self::URUTAN['terbaru'];

        $query->orderBy($kolom, $arah)->orderBy('id', $arah);
    }
}
