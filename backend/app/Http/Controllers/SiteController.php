<?php

namespace App\Http\Controllers;

use App\Enums\StatusDonasi;
use App\Http\Requests\DonasiPublikRequest;
use App\Http\Requests\KontakRequest;
use App\Models\AnakPanti;
use App\Models\Donasi;
use App\Models\Pesan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Public website pages. Artikel, program and galeri are still static sample
 * data from the Figma design; the list of children comes from anak_panti.
 */
class SiteController extends Controller
{
    public function home(): View
    {
        $programs = $this->programHighlights();

        return view('pages.beranda', [
            'programs' => [...$programs, ...$programs],
            'articles' => $this->articles(),
            'categories' => $this->categories(),
        ]);
    }

    public function programIndex(): View
    {
        return view('pages.program.index', [
            'programs' => array_fill(0, 6, $this->articles()[3]),
        ]);
    }

    public function programShow(string $slug): View
    {
        return $this->postDetail($slug, 'program');
    }

    public function artikelIndex(): View
    {
        return view('pages.artikel.index', [
            'articles' => $this->articles(),
            'categories' => $this->categories(),
        ]);
    }

    public function artikelShow(string $slug): View
    {
        return $this->postDetail($slug, 'artikel');
    }

    public function galeri(): View
    {
        $photos = collect(range(1, 9))->map(fn (int $number): array => [
            'path' => "images/galeri/{$number}.jpg",
            'exists' => file_exists(public_path("images/galeri/{$number}.jpg")),
            'caption' => "Kegiatan Panti Asuhan YASIBU {$number}",
        ]);

        return view('pages.tentang.galeri', ['photos' => $photos]);
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
    public function kirimDonasi(DonasiPublikRequest $request): RedirectResponse
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

        return view('pages.tentang.anak-asuh', [
            'children' => $children,
            'isDescending' => $isDescending,
        ]);
    }

    /**
     * Every detail page renders the sample article from the design.
     */
    private function postDetail(string $slug, string $section): View
    {
        $knownSlugs = collect([...$this->programHighlights(), ...$this->articles()])
            ->pluck('slug')
            ->push('10-ciri-orang-yang-mendapatkan-malam-lailatul-qadar');

        abort_unless($knownSlugs->contains($slug), 404);

        return view("pages.{$section}.show", [
            'post' => $this->post(
                '10-ciri-orang-yang-mendapatkan-malam-lailatul-qadar',
                '10 Ciri Orang yang Mendapatkan Malam Lailatul Qadar',
                'images/program/detail/lailatul-qadar.png',
                '2026-03-12',
            ),
            'categories' => $this->categories(),
            'recentPosts' => [...$this->programHighlights(), ...array_slice($this->articles(), 0, 2)],
            'donors' => [
                ['nama' => 'Hamba Allah', 'tanggal' => '23/03/2023', 'nominal' => 'Rp150.000,00', 'pesan' => 'Bismillah'],
                ['nama' => 'Hamba Allah', 'tanggal' => '23/03/2023', 'nominal' => 'Rp150.000,00', 'pesan' => 'Bismillah'],
            ],
        ]);
    }

    /**
     * @return list<array{slug: string, title: string, image: string, date: string, date_iso: string, excerpt: string}>
     */
    private function programHighlights(): array
    {
        return [
            $this->post(
                'nabi-yang-paling-kaya-benarkah-nabi-sulaiman-as',
                'Nabi yang Paling Kaya, Benarkah Nabi Sulaiman AS ?',
                'images/program/nabi-sulaiman.png',
                '2026-09-10',
                'Para nabi memiliki mukjizat yang diberikan Allah SWT, termasuk Nabi Sulaiman AS. Ia dikenal sebagai...',
            ),
            $this->post(
                '5-doa-mohon-perlindungan-dari-bencana-alam',
                '5 Doa Mohon Perlindungan dari Bencana Alam, Dibaca saat Gunung Meletus-Gempa Bumi',
                'images/program/doa-bencana.png',
                '2026-09-10',
                'Doa memohon perlindungan dari bencana alam bisa dibaca saat terjadi gunung meletus, gempa bumi ataupun...',
            ),
            $this->post(
                'foster-care-membuka-rumah-menghadirkan-keluarga',
                'Foster Care: Membuka Rumah, Menghadirkan Keluarga, Memberikan Harapan',
                'images/program/foster-care.png',
                '2026-08-19',
                'Foster care atau pengasuhan keluarga pengganti merupakan salah satu bentuk kepedulian terhadap anak-anak yang untuk...',
            ),
        ];
    }

    /**
     * @return list<array{slug: string, title: string, image: string, date: string, date_iso: string, excerpt: string}>
     */
    private function articles(): array
    {
        return [
            $this->post('maulid-nabi-1447h-di-yasibu', 'Maulid Nabi 1447H di Yasibu: Menebar Cinta, Meneladani Akhlak Rasulullah SAW', 'images/artikel/maulid-nabi.png', '2026-08-19'),
            $this->post('lebaran-yatim-momentum-indah-berbagi-kasih', 'LEBARAN YATIM MOMENTUM INDAH BERBAGI KASIH UNTUK ANAK YATIM', 'images/artikel/lebaran-yatim.png', '2026-06-04'),
            $this->post('qurban-bahagia-1447-h', 'QURBAN BAHAGIA 1447 H', 'images/artikel/qurban-bahagia.png', '2026-04-20'),
            $this->post('pahami-serba-serbi-ibadah-qurban', 'Pahami Serba-serbi Ibadah Qurban dan Syarat Pelaksanaannya', 'images/artikel/serba-serbi-qurban.png', '2026-04-20'),
            $this->post('amalan-sunnah-di-bulan-syawal', 'Amalan Sunnah di Bulan Syawal yang Bikin Pahala Terus Mengalir', 'images/artikel/amalan-syawal.png', '2026-04-13'),
            $this->post('8-amalan-saat-mudik', '8 Amalan Saat Mudik Agar Berkah Dan Aman Di Jalan', 'images/artikel/amalan-mudik.png', '2026-03-16'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function categories(): array
    {
        return ['Acara' => 9, 'Agama' => 16, 'Berita' => 9, 'Kegiatan' => 4, 'Pendidikan' => 5];
    }

    /**
     * @return array{slug: string, title: string, image: string, date: string, date_iso: string, excerpt: string}
     */
    private function post(string $slug, string $title, string $image, string $date, string $excerpt = ''): array
    {
        $publishedAt = Carbon::parse($date);

        return [
            'slug' => $slug,
            'title' => $title,
            'image' => $image,
            'date' => $publishedAt->format('d F Y'),
            'date_iso' => $publishedAt->toDateString(),
            'excerpt' => $excerpt,
        ];
    }
}
