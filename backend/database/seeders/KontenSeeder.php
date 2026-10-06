<?php

namespace Database\Seeders;

use App\Enums\StatusBerita;
use App\Models\Berita;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Isi contoh artikel kegiatan dan program agar halaman publik tidak kosong.
 *
 * Dilewati bila sudah ada artikel atau program, supaya tulisan pengurus tidak tertimpa.
 * Gambar contoh disalin dari public/images ke disk public seperti unggahan biasa.
 * Slug diisi langsung karena DatabaseSeeder mematikan event model.
 */
class KontenSeeder extends Seeder
{
    public function run(): void
    {
        if (Berita::query()->exists() || Program::query()->exists()) {
            return;
        }

        foreach ($this->artikel() as [$judul, $kategori, $gambar, $tanggal, $ringkasan, $isi]) {
            $berita = new Berita(['judul' => $judul, 'kategori' => $kategori, 'ringkasan' => $ringkasan, 'isi' => $isi]);
            $berita->slug = Str::slug($judul);
            $berita->status = StatusBerita::Terbit;
            $berita->tanggal_terbit = $tanggal;
            $berita->gambar = $this->salinGambar($gambar, Berita::GAMBAR_FOLDER);
            $berita->save();
        }

        foreach ($this->program() as [$judul, $kategori, $gambar, $tanggal, $ringkasan, $isi]) {
            $program = new Program(['judul' => $judul, 'kategori' => $kategori, 'ringkasan' => $ringkasan, 'isi' => $isi, 'tampil_di_beranda' => true]);
            $program->slug = Str::slug($judul);
            $program->status = StatusBerita::Terbit;
            $program->tanggal_terbit = $tanggal;
            $program->gambar = $this->salinGambar($gambar, Program::GAMBAR_FOLDER);
            $program->save();
        }
    }

    /**
     * Salin gambar contoh ke disk public, lalu kembalikan path-nya.
     */
    private function salinGambar(?string $gambar, string $folder): ?string
    {
        if ($gambar === null || ! is_file(public_path($gambar))) {
            return null;
        }

        return Storage::disk('public')->putFile($folder, new File(public_path($gambar)));
    }

    /**
     * @return list<array{0: string, 1: string, 2: ?string, 3: string, 4: string, 5: string}>
     */
    private function artikel(): array
    {
        return [
            [
                'Maulid Nabi 1447H di Yasibu: Menebar Cinta, Meneladani Akhlak Rasulullah SAW', 'Agama', 'images/artikel/maulid-nabi.png', '2026-08-19',
                'Peringatan Maulid Nabi bersama anak-anak asuh dan warga sekitar panti.',
                '<p>Panti Asuhan YASIBU memperingati Maulid Nabi Muhammad SAW 1447H bersama anak-anak asuh, pengurus, dan warga sekitar.</p><p>Acara diisi dengan pembacaan shalawat, tausiyah tentang akhlak Rasulullah, dan makan bersama.</p>',
            ],
            [
                'Lebaran Yatim: Momentum Indah Berbagi Kasih untuk Anak Yatim', 'Kegiatan', 'images/artikel/lebaran-yatim.png', '2026-06-04',
                'Santunan dan kebersamaan bersama anak yatim di hari Lebaran Yatim.',
                '<p>Pada 10 Muharram, para donatur hadir di panti untuk berbagi kebahagiaan bersama anak-anak yatim.</p><p>Terima kasih kepada seluruh donatur yang telah mendukung kegiatan ini.</p>',
            ],
            [
                'Qurban Bahagia 1447 H', 'Acara', 'images/artikel/qurban-bahagia.png', '2026-04-20',
                'Penyembelihan dan pembagian hewan qurban di lingkungan panti.',
                '<p>Alhamdulillah, panti menerima titipan hewan qurban dari para donatur dan membagikan dagingnya kepada anak asuh serta warga sekitar.</p>',
            ],
            [
                'Pahami Serba-serbi Ibadah Qurban dan Syarat Pelaksanaannya', 'Agama', 'images/artikel/serba-serbi-qurban.png', '2026-04-20',
                'Ketentuan hewan qurban, waktu penyembelihan, dan pembagian dagingnya.',
                '<p>Qurban adalah ibadah menyembelih hewan ternak pada Hari Raya Idul Adha dan hari tasyrik.</p><h2>Syarat hewan qurban</h2><ul><li>Cukup umur</li><li>Sehat dan tidak cacat</li><li>Milik penuh orang yang berqurban</li></ul>',
            ],
            [
                'Amalan Sunnah di Bulan Syawal yang Bikin Pahala Terus Mengalir', 'Agama', 'images/artikel/amalan-syawal.png', '2026-04-13',
                'Puasa enam hari, silaturahmi, dan amalan lain di bulan Syawal.',
                '<p>Bulan Syawal adalah kesempatan menjaga semangat ibadah setelah Ramadhan.</p><ol><li>Puasa enam hari Syawal</li><li>Menyambung silaturahmi</li><li>Bersedekah</li></ol>',
            ],
            [
                '8 Amalan Saat Mudik agar Berkah dan Aman di Jalan', 'Agama', 'images/artikel/amalan-mudik.png', '2026-03-16',
                'Doa dan amalan yang dianjurkan selama perjalanan mudik.',
                '<p>Perjalanan mudik bisa menjadi ibadah bila diniatkan untuk menyambung silaturahmi.</p><p>Di antaranya membaca doa safar, bersedekah sebelum berangkat, dan menjaga shalat selama perjalanan.</p>',
            ],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: ?string, 3: string, 4: string, 5: string}>
     */
    private function program(): array
    {
        return [
            [
                'Beasiswa Pendidikan Anak Yatim dan Dhuafa', 'Beasiswa', 'images/program/foster-care.png', '2026-09-10',
                'Bantuan biaya sekolah agar anak yatim dan dhuafa tetap bersekolah hingga lulus.',
                '<p>Program beasiswa membantu biaya sekolah, seragam, dan perlengkapan belajar anak asuh.</p><h2>Sasaran program</h2><ul><li>Anak yatim usia SD hingga SMA</li><li>Anak dhuafa berprestasi</li></ul>',
            ],
            [
                'Tahfidz Al-Quran', 'Pendidikan', 'images/program/detail/lailatul-qadar.png', '2026-09-01',
                'Pembinaan hafalan Al-Quran setiap hari selepas Subuh dan Maghrib.',
                '<p>Anak-anak asuh dibimbing menghafal Al-Quran dengan target hafalan sesuai kemampuan masing-masing.</p>',
            ],
            [
                'Santunan dan Pendampingan Keluarga Dhuafa', 'Sosial', 'images/program/doa-bencana.png', '2026-08-19',
                'Santunan rutin dan pendampingan bagi keluarga dhuafa di sekitar panti.',
                '<p>Panti menyalurkan santunan bulanan dan mendampingi keluarga dhuafa agar anak-anak mereka tetap mendapat pengasuhan yang layak.</p>',
            ],
        ];
    }
}
