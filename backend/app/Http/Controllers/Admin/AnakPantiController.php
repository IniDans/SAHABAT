<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnakPantiRequest;
use App\Models\AnakPanti;
use App\Models\WaliAnak;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AnakPantiController extends Controller
{
    /**
     * Parameter query yang dipakai untuk menyaring daftar anak.
     */
    private const FILTER = ['search', 'status', 'keterangan', 'jenis_kelamin', 'kesehatan'];

    /**
     * Daftar anak dengan ringkasan jumlah, filter, serta dialog edit dan export.
     */
    public function index(Request $request): View
    {
        $filter = $request->only(self::FILTER);

        $anak = $this->saring(AnakPanti::query(), $filter)
            ->with('wali')
            ->orderByRaw('Status_Asuh = ?', [StatusAsuh::Alumni->value])
            ->orderBy('Nama')
            ->paginate(10)
            ->withQueryString();

        $jumlahPerStatus = AnakPanti::query()
            ->selectRaw('Status_Asuh, count(*) as jumlah')
            ->groupBy('Status_Asuh')
            ->pluck('jumlah', 'Status_Asuh')
            ->map(fn ($jumlah): int => (int) $jumlah);

        return view('admin.anak-panti.index', [
            'anak' => $anak,
            'filter' => array_filter($filter, filled(...)),
            'jumlahTotal' => $jumlahPerStatus->sum(),
            'jumlahAktif' => $jumlahPerStatus->get(StatusAsuh::MasihAktif->value, 0),
            'jumlahAlumni' => $jumlahPerStatus->get(StatusAsuh::Alumni->value, 0),
            'jumlahCatatanKesehatan' => $this->denganCatatanKesehatan(AnakPanti::query()->aktif())->count(),
            'jumlahSesuaiFilter' => $anak->total(),
            'pilihan' => collect(array_keys(AnakPanti::PILIHAN))->mapWithKeys(fn (string $kolom) => [$kolom => AnakPanti::pilihan($kolom)]),
            'wali' => WaliAnak::query()->orderBy('Nama_Wali')->get(['ID_Wali', 'Nama_Wali']),
        ]);
    }

    public function store(AnakPantiRequest $request): RedirectResponse
    {
        $anak = AnakPanti::create($request->validated());

        return to_route('admin.anak-panti.index', ['search' => $anak->NIK])->with('status', "Data {$anak->Nama} berhasil ditambahkan.");
    }

    public function update(AnakPantiRequest $request, AnakPanti $anakPanti): RedirectResponse
    {
        $anakPanti->update($request->validated());

        return back()->with('status', "Data {$anakPanti->Nama} berhasil disimpan.");
    }

    /**
     * Hanya alumni yang boleh dihapus, sama dengan trigger trg_cegah_hapus_anak_aktif.
     */
    public function destroy(AnakPanti $anakPanti): RedirectResponse
    {
        if (! $anakPanti->isDeletable()) {
            return back()->withErrors(['hapus' => "{$anakPanti->Nama} masih aktif, jadi tidak dapat dihapus. Ubah status asuh menjadi Alumni terlebih dulu."]);
        }

        $anakPanti->delete();

        return back()->with('status', "Data {$anakPanti->Nama} berhasil dihapus.");
    }

    /**
     * Unduh data anak sebagai Excel, CSV, atau PDF dengan kolom yang dipilih.
     */
    public function ekspor(Request $request): Response
    {
        $data = $request->validate([
            'format' => ['required', Rule::in(['xlsx', 'csv', 'pdf'])],
            'cakupan' => ['required', Rule::in(['semua', 'filter', 'dipilih'])],
            'kolom' => ['required', 'array'],
            'kolom.*' => [Rule::in(array_keys(AnakPanti::KOLOM_EKSPOR))],
            'nik' => ['required_if:cakupan,dipilih', 'array'],
            'nik.*' => ['string'],
            'filter' => ['array'],
        ], [
            'kolom.required' => 'Pilih minimal satu kolom.',
            'nik.required_if' => 'Pilih minimal satu anak di daftar terlebih dulu.',
        ]);

        $query = AnakPanti::query()->with('wali')->orderBy('Nama');

        match ($data['cakupan']) {
            'filter' => $this->saring($query, Arr::only($data['filter'] ?? [], self::FILTER)),
            'dipilih' => $query->whereKey($data['nik']),
            'semua' => null,
        };

        // Urutan kolom mengikuti daftar kolom, bukan urutan centang.
        $kolom = array_values(array_intersect(array_keys(AnakPanti::KOLOM_EKSPOR), $data['kolom']));
        $anak = $query->get();
        $namaBerkas = 'data-anak-panti-'.now()->format('Y-m-d').'.'.$data['format'];

        if ($data['format'] === 'pdf') {
            return Pdf::loadView('admin.anak-panti.pdf', [
                'anak' => $anak,
                'kolom' => Arr::only(AnakPanti::KOLOM_EKSPOR, $kolom),
            ])->setPaper('a4', count($kolom) > 6 ? 'landscape' : 'portrait')->download($namaBerkas);
        }

        return $this->lembarKerja($data['format'], $anak, $kolom, $namaBerkas);
    }

    /**
     * Tulis data ke berkas xlsx/csv sementara lalu kirim sebagai unduhan.
     *
     * @param  Collection<int, AnakPanti>  $anak
     * @param  list<string>  $kolom
     */
    private function lembarKerja(string $format, Collection $anak, array $kolom, string $namaBerkas): BinaryFileResponse
    {
        $berkas = tempnam(sys_get_temp_dir(), 'ekspor-anak-');
        $writer = $format === 'csv' ? new CsvWriter : new XlsxWriter;
        $writer->openToFile($berkas);

        $judul = array_map(fn (string $kunci): string => AnakPanti::KOLOM_EKSPOR[$kunci], $kolom);
        $writer->addRow(Row::fromValuesWithStyle($judul, new Style(fontBold: true)));

        foreach ($anak as $item) {
            $writer->addRow(Row::fromValues(array_map(fn (string $kunci): string => $item->nilaiEkspor($kunci), $kolom)));
        }

        $writer->close();

        return response()->download($berkas, $namaBerkas, [
            'Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    /**
     * Terapkan filter daftar: search (nama/NIK), status asuh, status anak, jenis kelamin, catatan kesehatan.
     *
     * @param  Builder<AnakPanti>  $query
     * @param  array<string, mixed>  $filter
     * @return Builder<AnakPanti>
     */
    private function saring(Builder $query, array $filter): Builder
    {
        $teks = fn (string $kunci): string => is_string($filter[$kunci] ?? null) ? trim($filter[$kunci]) : '';

        return $query
            ->when($teks('search'), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('Nama', 'like', "%{$search}%")
                ->orWhere('NIK', 'like', "%{$search}%")))
            ->when(StatusAsuh::tryFrom($teks('status')), fn ($q, $status) => $q->where('Status_Asuh', $status))
            ->when($teks('keterangan'), fn ($q, $keterangan) => $q->where('Keterangan', $keterangan))
            ->when(JenisKelamin::tryFrom($teks('jenis_kelamin')), fn ($q, $jenisKelamin) => $q->where('Jenis_Kelamin', $jenisKelamin))
            ->when($teks('kesehatan') === 'catatan', fn ($q) => $this->denganCatatanKesehatan($q));
    }

    /**
     * @param  Builder<AnakPanti>  $query
     * @return Builder<AnakPanti>
     */
    private function denganCatatanKesehatan(Builder $query): Builder
    {
        return $query->whereNotNull('Kesehatan')
            ->where('Kesehatan', '!=', '')
            ->whereRaw('lower(Kesehatan) != ?', ['sehat']);
    }
}
