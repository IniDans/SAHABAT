<?php

namespace Database\Seeders;

use App\Enums\StatusBerita;
use App\Models\Berita;
use App\Models\FotoGaleri;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Isi contoh artikel, program, dan galeri agar halaman publik tidak kosong.
 *
 * Dilewati bila pengurus sudah menulis artikel atau program sendiri (slug di luar daftar contoh),
 * supaya contoh tidak bercampur dengan tulisan asli. Contoh yang sudah ada tidak dibuat ulang,
 * jadi contoh baru di daftar ini bisa ditambahkan ke database demo dengan menjalankan seeder lagi.
 * Galeri hanya diisi bila masih kosong.
 *
 * Gambar contoh disalin dari public/images ke disk public seperti unggahan biasa.
 * Slug diisi langsung karena DatabaseSeeder mematikan event model.
 */
class KontenSeeder extends Seeder
{
    public function run(): void
    {
        $artikel = $this->artikel();
        $program = $this->program();

        if ($this->adaTulisanPengurus(Berita::class, $artikel) || $this->adaTulisanPengurus(Program::class, $program)) {
            return;
        }

        $this->isiTulisan(Berita::class, $artikel);
        $this->isiTulisan(Program::class, $program, ['tampil_di_beranda' => true]);
        $this->isiGaleri();
    }

    /**
     * @param  class-string<Berita|Program>  $model
     * @param  list<array{0: string, 1: string, 2: ?string, 3: string, 4: string, 5: string}>  $contoh
     */
    private function adaTulisanPengurus(string $model, array $contoh): bool
    {
        return $model::query()
            ->whereNotIn('slug', array_map(fn (array $tulisan): string => Str::slug($tulisan[0]), $contoh))
            ->exists();
    }

    /**
     * Simpan tulisan contoh yang belum ada sebagai tulisan terbit.
     *
     * @param  class-string<Berita|Program>  $model
     * @param  list<array{0: string, 1: string, 2: ?string, 3: string, 4: string, 5: string}>  $contoh
     * @param  array<string, mixed>  $kolomTambahan
     */
    private function isiTulisan(string $model, array $contoh, array $kolomTambahan = []): void
    {
        $slugTersimpan = $model::query()->pluck('slug')->all();

        foreach ($contoh as [$judul, $kategori, $gambar, $tanggal, $ringkasan, $isi]) {
            $slug = Str::slug($judul);

            if (in_array($slug, $slugTersimpan, true)) {
                continue;
            }

            $tulisan = new $model(['judul' => $judul, 'kategori' => $kategori, 'ringkasan' => $ringkasan, 'isi' => $isi, ...$kolomTambahan]);
            $tulisan->slug = $slug;
            $tulisan->status = StatusBerita::Terbit;
            $tulisan->tanggal_terbit = $tanggal;
            $tulisan->gambar = $this->salinGambar($gambar, $model::GAMBAR_FOLDER);
            $tulisan->save();
        }
    }

    private function isiGaleri(): void
    {
        if (FotoGaleri::query()->exists()) {
            return;
        }

        foreach ($this->galeri() as $index => [$gambar, $keterangan]) {
            $path = $this->salinGambar($gambar, FotoGaleri::GAMBAR_FOLDER);

            if ($path === null) {
                continue;
            }

            $foto = new FotoGaleri(['keterangan' => $keterangan]);
            $foto->gambar = $path;
            $foto->urutan = $index + 1;
            $foto->save();
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
            [
                'Wisata Edukasi Anak Asuh di Akhir Pekan', 'Kegiatan', 'images/artikel/amalan-mudik.png', '2026-11-02',
                'Anak-anak asuh belajar mengenal tanaman dan satwa sambil berlibur bersama pengasuh.',
                '<p>Sabtu pagi, anak-anak asuh berangkat bersama para pengasuh untuk wisata edukasi akhir pekan. Kegiatan ini menjadi hadiah setelah anak-anak menyelesaikan ujian tengah semester.</p><p>Di lokasi, anak-anak dibagi ke beberapa kelompok kecil. Setiap kelompok mencatat nama tanaman dan hewan yang mereka temui, lalu menceritakannya kembali di depan teman-teman saat istirahat siang.</p><p>Terima kasih kepada donatur yang menanggung biaya transportasi dan konsumsi.</p>',
            ],
            [
                'Kerja Bakti Bersama Warga Membersihkan Lingkungan Panti', 'Berita', null, '2026-10-26',
                'Anak asuh, pengurus, dan warga sekitar membersihkan saluran air dan halaman panti menjelang musim hujan.',
                '<p>Menjelang musim hujan, Panti Asuhan YASIBU mengadakan kerja bakti bersama warga sekitar. Sejak pukul tujuh pagi, saluran air di depan panti dibersihkan dari sampah dan endapan lumpur.</p><p>Anak-anak yang lebih besar membantu menyapu halaman dan merapikan taman, sementara ibu-ibu warga menyiapkan sarapan bersama. Kegiatan selesai menjelang Dzuhur.</p>',
            ],
            [
                'Pemeriksaan Kesehatan Rutin untuk Anak Asuh', 'Berita', null, '2026-10-12',
                'Petugas puskesmas memeriksa berat badan, tinggi badan, dan kesehatan gigi anak-anak asuh.',
                '<p>Setiap tiga bulan, anak-anak asuh mendapat pemeriksaan kesehatan dari petugas puskesmas. Hasil penimbangan dicatat pengasuh agar perkembangan gizi setiap anak bisa dipantau.</p><h2>Yang diperiksa</h2><ul><li>Berat dan tinggi badan</li><li>Kesehatan gigi dan mulut</li><li>Keluhan kesehatan umum</li></ul>',
            ],
            [
                'Tips Mendampingi Anak Belajar di Rumah', 'Pendidikan', 'images/artikel/amalan-syawal.png', '2026-10-05',
                'Cara sederhana yang dipakai pengasuh agar anak betah belajar tanpa merasa dipaksa.',
                '<p>Anak lebih mudah fokus bila belajar dengan jadwal yang tetap dan suasana yang tenang. Berikut kebiasaan yang diterapkan pengasuh di panti.</p><ol><li><strong>Waktu belajar yang sama setiap hari.</strong> Di panti, jam belajar dimulai selepas Isya dan berlangsung satu jam.</li><li><strong>Mulai dari pelajaran yang disukai.</strong> Semangat dari pelajaran pertama terbawa ke pelajaran berikutnya.</li><li><strong>Kakak membantu adik.</strong> Anak SMP dan SMA mendampingi adik-adik SD mengerjakan PR.</li><li><strong>Puji usahanya, bukan hanya nilainya.</strong> Anak yang merasa usahanya dihargai lebih berani mencoba soal sulit.</li></ol>',
            ],
            [
                'Panti Menerima Kunjungan dan Santunan dari Donatur', 'Berita', 'images/hero-1.png', '2026-09-27',
                'Rombongan donatur berkunjung, berbincang dengan anak-anak, dan menyerahkan santunan.',
                '<p>Panti Asuhan YASIBU menerima kunjungan rombongan donatur di aula panti. Acara dibuka dengan pembacaan ayat suci Al-Quran oleh salah satu anak asuh, dilanjutkan sambutan dari pengurus yayasan.</p><p>Setelah penyerahan santunan, para tamu berbincang langsung dengan anak-anak dan berfoto bersama. Pengurus menyampaikan terima kasih atas perhatian dan dukungan yang terus mengalir untuk panti.</p>',
            ],
            [
                'Bimbingan Belajar Malam Menjelang Ujian Akhir Semester', 'Pendidikan', null, '2026-09-15',
                'Relawan pengajar mendampingi anak-anak kelas 6, 9, dan 12 berlatih soal setiap malam.',
                '<p>Menjelang ujian akhir semester, panti membuka kelas bimbingan belajar malam untuk anak-anak kelas 6 SD, 9 SMP, dan 12 SMA. Kelas berlangsung empat malam dalam seminggu dengan pendampingan relawan pengajar.</p><p>Materi difokuskan pada Matematika, Bahasa Indonesia, dan IPA. Setiap pekan anak-anak mengerjakan latihan soal agar terbiasa dengan bentuk soal ujian.</p><p>Bagi yang ingin menjadi relawan pengajar, silakan hubungi pengurus melalui halaman Kontak.</p>',
            ],
        ];
    }

    /**
     * Foto galeri contoh: path gambar di public/images dan keterangan (null = tanpa keterangan).
     *
     * @return list<array{0: string, 1: ?string}>
     */
    private function galeri(): array
    {
        return [
            ['images/hero-1.png', 'Foto bersama anak asuh, pengurus, dan tamu di aula panti'],
            ['images/artikel/lebaran-yatim.png', 'Santunan Lebaran Yatim 10 Muharram'],
            ['images/artikel/maulid-nabi.png', 'Peringatan Maulid Nabi bersama anak asuh'],
            ['images/artikel/qurban-bahagia.png', 'Penyembelihan hewan qurban di halaman panti'],
            ['images/artikel/amalan-syawal.png', 'Setoran hafalan Al-Quran selepas Maghrib'],
            ['images/artikel/amalan-mudik.png', null],
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
