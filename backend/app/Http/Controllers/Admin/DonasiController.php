<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DonasiRequest;
use App\Models\Donasi;
use App\Models\KebutuhanPanti;
use App\Services\KuitansiDonasi;
use App\Services\PembacaBuktiTransfer;
use App\Services\PemberitahuDonatur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
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
            ->with('kebutuhan')
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
            fputcsv($out, ['Tanggal', 'Nama donatur', 'Ditampilkan sebagai', 'No WhatsApp', 'Email', 'Alamat', 'Program', 'Kebutuhan', 'Nominal', 'Metode', 'Status', 'Keterangan']);

            foreach ($query->with('kebutuhan')->lazy() as $item) {
                fputcsv($out, [
                    $item->tanggal_donasi->toDateString(),
                    $item->nama_donatur,
                    $item->tampil_sebagai->value,
                    $item->no_whatsapp,
                    $item->email,
                    $item->alamat,
                    $item->program->value,
                    $item->kebutuhan?->label(),
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
        return $this->form(new Donasi(['tanggal_donasi' => today()]));
    }

    /**
     * Simpan donasi baru. Donasi yang langsung dicatat diterima/ditolak diberitahukan ke donatur lewat email.
     */
    public function store(DonasiRequest $request, PemberitahuDonatur $pemberitahu): RedirectResponse
    {
        $donasi = Donasi::create($request->validated());

        if ($donasi->status !== StatusDonasi::Menunggu) {
            $pemberitahu->kirim($donasi);
        }

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil dicatat.');
    }

    /**
     * Form ubah donasi.
     */
    public function edit(Donasi $donasi): View
    {
        return $this->form($donasi);
    }

    /**
     * Simpan perubahan donasi. Bila status berubah, donatur diberi tahu lewat email.
     */
    public function update(DonasiRequest $request, Donasi $donasi, PemberitahuDonatur $pemberitahu): RedirectResponse
    {
        $donasi->update($request->validated());

        $this->beritahuBilaStatusBerubah($donasi, $pemberitahu);

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil diperbarui.');
    }

    /**
     * Ubah status donasi langsung dari daftar (terima/tolak), lalu beri tahu donatur lewat email.
     */
    public function updateStatus(Request $request, Donasi $donasi, PemberitahuDonatur $pemberitahu): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(StatusDonasi::class)],
        ]);

        $donasi->update($validated);

        $this->beritahuBilaStatusBerubah($donasi, $pemberitahu);

        return back()->with('status', "Donasi {$donasi->nama_donatur} ditandai {$donasi->status->value}.");
    }

    /**
     * Unduh kuitansi PDF donasi yang sudah diterima.
     */
    public function kuitansi(Donasi $donasi): Response
    {
        abort_unless($donasi->status === StatusDonasi::Diterima, 404);

        return KuitansiDonasi::pdf($donasi)->download(KuitansiDonasi::namaFile($donasi));
    }

    /**
     * Hapus catatan donasi.
     */
    public function destroy(Donasi $donasi): RedirectResponse
    {
        $bukti = $donasi->buktiPath();

        $donasi->delete();

        if ($bukti) {
            Storage::delete($bukti);
        }

        return to_route('admin.donasi.index')->with('status', 'Donasi berhasil dihapus.');
    }

    /**
     * Tampilkan foto bukti transfer dari disk privat.
     */
    public function bukti(Donasi $donasi): StreamedResponse
    {
        $bukti = $donasi->buktiPath();

        abort_unless($bukti && Storage::exists($bukti), 404);

        return Storage::response($bukti);
    }

    /**
     * Baca ulang bukti transfer dengan AI, mis. setelah pembacaan sebelumnya gagal.
     */
    public function bacaBukti(Donasi $donasi, PembacaBuktiTransfer $pembaca): RedirectResponse
    {
        abort_unless($donasi->buktiPath(), 404);

        $pembaca->bacaDonasi($donasi);

        return back()->with('status', 'Bukti transfer sudah dibaca ulang.');
    }

    private function form(Donasi $donasi): View
    {
        return view('admin.donasi.form', [
            'donasi' => $donasi,
            'pilihanKebutuhan' => KebutuhanPanti::query()
                ->where(fn (Builder $query) => $query->where('terpenuhi', false)->when($donasi->kebutuhan_panti_id, fn ($q, $id) => $q->orWhereKey($id)))
                ->palingMendesak()
                ->get(),
        ]);
    }

    /**
     * Email donatur hanya bila status memang berubah ke Diterima atau Ditolak.
     */
    private function beritahuBilaStatusBerubah(Donasi $donasi, PemberitahuDonatur $pemberitahu): void
    {
        if ($donasi->wasChanged('status') && $donasi->status !== StatusDonasi::Menunggu) {
            $pemberitahu->kirim($donasi);
        }
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
