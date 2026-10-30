<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanPindaianRequest;
use App\Models\AnakPanti;
use App\Models\WaliAnak;
use App\Services\PembacaDokumenAnak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

/**
 * Pendataan anak dari scan KK atau akta kelahiran: AI membaca dokumen, admin memeriksa hasilnya, lalu menyimpan.
 * Berkas dokumen tidak disimpan; hasil baca disimpan di sesi sampai disimpan atau dibatalkan.
 */
class PindaiDokumenController extends Controller
{
    private const SESI = 'pindai_anak';

    /**
     * Kolom anak_panti yang diisi dari form tinjauan.
     */
    private const KOLOM = ['NIK', 'Nama', 'Jenis_Kelamin', 'Tempat_Lahir', 'Tanggal_Lahir', 'Agama', 'Keterangan', 'Status_Anak', 'Pendidikan', 'Status_Asuh', 'Kesehatan'];

    public function baca(Request $request, PembacaDokumenAnak $pembaca): RedirectResponse
    {
        $request->validate([
            'dokumen' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ], [
            'dokumen.required' => 'Pilih foto atau PDF KK/akta kelahiran.',
            'dokumen.mimes' => 'Dokumen harus berformat JPG, PNG, WEBP, atau PDF.',
            'dokumen.max' => 'Ukuran dokumen maksimal 4 MB.',
        ]);

        $berkas = $request->file('dokumen');

        try {
            $hasil = $pembaca->baca($berkas->get(), $berkas->getMimeType());
        } catch (RuntimeException $e) {
            return back()->withErrors(['dokumen' => "Dokumen belum terbaca: {$e->getMessage()}"]);
        }

        if ($hasil['jenis_dokumen'] === 'lainnya' || ! $hasil['anggota']) {
            return back()->withErrors(['dokumen' => trim('Dokumen tidak terbaca sebagai KK atau akta kelahiran. '.($hasil['catatan'] ?? ''))]);
        }

        $request->session()->put(self::SESI, $pembaca->untukForm($hasil));

        return to_route('admin.anak-panti.pindai');
    }

    /**
     * Form tinjauan hasil baca. Tanpa hasil baca di sesi, kembali ke daftar anak.
     */
    public function tinjau(Request $request): View|RedirectResponse
    {
        $hasil = $request->session()->get(self::SESI);

        if (! $hasil) {
            return to_route('admin.anak-panti.index');
        }

        return view('admin.anak-panti.pindai', [
            'hasil' => $hasil,
            'pilihan' => collect(array_keys(AnakPanti::PILIHAN))->mapWithKeys(fn (string $kolom) => [$kolom => AnakPanti::pilihan($kolom)]),
            'wali' => WaliAnak::query()->orderBy('Nama_Wali')->get(['ID_Wali', 'Nama_Wali']),
        ]);
    }

    public function simpan(SimpanPindaianRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $anak = DB::transaction(function () use ($data) {
            $idWali = match ($data['wali'] ?? null) {
                null => null,
                'baru' => WaliAnak::create(Arr::only($data, ['Nama_Wali', 'Alamat_Wali']))->ID_Wali,
                default => (int) $data['wali'],
            };

            return collect($data['anak'])->map(fn (array $baris): AnakPanti => AnakPanti::create([
                ...Arr::only($baris, self::KOLOM),
                'ID_Wali' => $idWali,
            ]));
        });

        $request->session()->forget(self::SESI);

        return to_route('admin.anak-panti.index', $anak->count() === 1 ? ['search' => $anak->first()->NIK] : [])
            ->with('status', 'Data '.$anak->pluck('Nama')->join(', ', ' dan ').' berhasil ditambahkan dari dokumen.')
            ->with('anak_baru', $anak->pluck('NIK')->all());
    }

    public function batal(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESI);

        return to_route('admin.anak-panti.index');
    }
}
