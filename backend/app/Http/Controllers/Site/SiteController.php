<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\DonasiRequest;
use App\Http\Requests\Site\KontakRequest;
use App\Models\AnakPanti;
use App\Models\Berita;
use App\Models\Donasi;
use App\Models\FotoGaleri;
use App\Models\KebutuhanPanti;
use App\Models\Pesan;
use App\Models\ProfilPanti;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman website publik. Artikel dan program diambil dari tulisan yang terbit di panel admin;
 * galeri dari foto yang diunggah admin; daftar anak asuh dari anak_panti.
 */
class SiteController extends Controller
{
    public function home(): View
    {
        return view('site.beranda', [
            'programs' => Program::terbit()->where('tampil_di_beranda', true)->limit(6)->get(),
            'articles' => Berita::terbit()->limit(6)->get(),
            'categories' => Berita::jumlahTerbitPerKategori(),
        ]);
    }

    public function programIndex(Request $request): View
    {
        return $this->daftarTulisan($request, Program::class, 'site.program.index');
    }

    public function programShow(string $slug): View
    {
        return $this->detailTulisan(Program::class, $slug, 'site.program.show');
    }

    public function artikelIndex(Request $request): View
    {
        return $this->daftarTulisan($request, Berita::class, 'site.artikel.index');
    }

    public function artikelShow(string $slug): View
    {
        return $this->detailTulisan(Berita::class, $slug, 'site.artikel.show');
    }

    /**
     * Formulir donasi, dengan daftar kebutuhan panti yang belum terpenuhi.
     */
    public function formulirDonasi(): View
    {
        return view('site.donasi.formulir', [
            'kebutuhan' => KebutuhanPanti::where('terpenuhi', false)->palingMendesak()->limit(8)->get(),
        ]);
    }

    public function profil(): View
    {
        return view('site.tentang.profil', ['profil' => ProfilPanti::bagian('lembaga')]);
    }

    public function visiMisi(): View
    {
        return view('site.tentang.visi-misi', ['profil' => ProfilPanti::bagian('visi_misi')]);
    }

    public function pengurus(): View
    {
        return view('site.tentang.pengurus', ['profil' => ProfilPanti::bagian('pengurus')]);
    }

    public function kontak(): View
    {
        return view('site.tentang.kontak', ['profil' => ProfilPanti::bagian('kontak')]);
    }

    public function galeri(): View
    {
        return view('site.tentang.galeri', [
            'foto' => FotoGaleri::query()->urut()->paginate(9),
        ]);
    }

    /**
     * Simpan pesan dari formulir Kontak agar muncul di menu Pesan masuk admin.
     */
    public function kirimPesan(KontakRequest $request): RedirectResponse
    {
        Pesan::create($request->validated());

        return to_route('tentang.kontak')
            ->with('status', 'Terima kasih, pesan Anda sudah terkirim. Kami akan segera membalasnya.');
    }

    /**
     * Catat donasi dari formulir publik dengan status Menunggu sampai dana dicek admin.
     */
    public function kirimDonasi(DonasiRequest $request): RedirectResponse
    {
        $donasi = Donasi::create([
            ...$request->validated(),
            'tanggal_donasi' => today(),
            'status' => StatusDonasi::Menunggu,
        ]);

        return to_route('donasi.rekening')->with('status', sprintf(
            'Terima kasih, %s. Donasi %s sebesar %s sudah tercatat. Silakan transfer melalui %s ke rekening di bawah ini.',
            $donasi->nama_donatur,
            $donasi->program->value,
            Donasi::rupiah($donasi->nominal),
            $donasi->metode_pembayaran->value,
        ));
    }

    public function anakAsuh(Request $request): View
    {
        $isDescending = $request->query('urut') === 'desc';

        $children = AnakPanti::aktif()
            ->when($request->filled('q'), fn ($query) => $query->where('Nama', 'like', '%'.$request->string('q')->trim().'%'))
            ->orderBy('Nama', $isDescending ? 'desc' : 'asc')
            ->paginate(25, ['Nama', 'Jenis_Kelamin', 'Pendidikan'])
            ->withQueryString();

        return view('site.tentang.anak-asuh', [
            'children' => $children,
            'isDescending' => $isDescending,
        ]);
    }

    /**
     * Daftar tulisan terbit. Filter: kategori (tidak peka huruf besar/kecil) dan q (judul).
     *
     * @param  class-string<Berita|Program>  $kelas
     */
    private function daftarTulisan(Request $request, string $kelas, string $view): View
    {
        $categories = $kelas::jumlahTerbitPerKategori();
        $dicari = mb_strtolower($request->string('kategori')->trim()->value());
        $kategori = collect($categories)->keys()->first(fn (string $nama): bool => mb_strtolower($nama) === $dicari);

        $tulisan = $kelas::terbit()
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->when($request->string('q')->trim()->value(), fn ($q, $cari) => $q->where('judul', 'like', "%{$cari}%"))
            ->paginate(9)
            ->withQueryString();

        return view($view, [
            'tulisan' => $tulisan,
            'kategori' => $kategori,
            'categories' => $categories,
        ]);
    }

    /**
     * Halaman detail tulisan terbit, dengan sidebar kategori, artikel terbaru, dan daftar donatur.
     *
     * @param  class-string<Berita|Program>  $kelas
     */
    private function detailTulisan(string $kelas, string $slug, string $view): View
    {
        $post = $kelas::terbit()->where('slug', $slug)->firstOrFail();

        return view($view, [
            'post' => $post,
            'categories' => $kelas::jumlahTerbitPerKategori(),
            'recentPosts' => Berita::terbit()
                ->when($post instanceof Berita, fn ($q) => $q->whereKeyNot($post->getKey()))
                ->limit(5)
                ->get(),
            'donors' => Donasi::diterima()
                ->latest('tanggal_donasi')
                ->latest('id')
                ->limit(50)
                ->get(),
            'programDonasi' => ProgramDonasi::tryFrom($post->kategori),
        ]);
    }
}
