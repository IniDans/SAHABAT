<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Isi halaman Tentang Kami (profil, visi misi, pengurus, kontak) dan footer website.
 * Satu baris per bagian; bagian yang belum pernah disimpan memakai isi bawaan.
 */
#[Table('profil_panti', key: 'kunci', keyType: 'string', incrementing: false)]
#[Fillable(['kunci', 'nilai'])]
class ProfilPanti extends Model
{
    public const CACHE_KEY = 'profil_panti';

    /**
     * Bagian yang bisa diubah admin, beserta judul tab di panel admin.
     *
     * @var array<string, string>
     */
    public const BAGIAN = [
        'lembaga' => 'Profil lembaga',
        'visi_misi' => 'Visi dan misi',
        'pengurus' => 'Pengurus',
        'kontak' => 'Kontak dan alamat',
        'sosial' => 'Media sosial',
    ];

    /**
     * Media sosial di footer website.
     *
     * @var array<string, string>
     */
    public const MEDIA_SOSIAL = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'array',
        ];
    }

    /**
     * Semua bagian profil, isi tersimpan digabung dengan isi bawaan.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function semua(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $tersimpan = static::query()->pluck('nilai', 'kunci');

            return collect(self::bawaan())
                ->map(fn (array $isi, string $bagian): array => [...$isi, ...($tersimpan[$bagian] ?? [])])
                ->all();
        });
    }

    /**
     * Isi satu bagian profil.
     *
     * @return array<string, mixed>
     */
    public static function bagian(string $bagian): array
    {
        return self::semua()[$bagian];
    }

    /**
     * Simpan satu bagian profil lalu kosongkan cache agar website langsung memakai isi baru.
     *
     * @param  array<string, mixed>  $nilai
     */
    public static function simpan(string $bagian, array $nilai): void
    {
        static::query()->updateOrCreate(['kunci' => $bagian], ['nilai' => $nilai]);

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Alamat peta Google Maps untuk iframe di halaman Kontak.
     */
    public static function urlPeta(string $alamat): string
    {
        return 'https://maps.google.com/maps?'.http_build_query(['q' => $alamat, 'z' => 16, 'output' => 'embed']);
    }

    /**
     * Isi bawaan, sama dengan isi website sebelum bisa diatur dari panel admin.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function bawaan(): array
    {
        return [
            'lembaga' => [
                'judul_legalitas' => 'Legalistas Yayasan Insan Indonesia Bersatu Malang',
                'legalitas' => [
                    ['label' => 'Akte Notaris', 'nilai' => 'Nomor 10, tanggal 14 Mei 2019 oleh IKA DYAH WARSITO, S.H., M.HUM., M.KN.'],
                    ['label' => 'SK Menkumham RI', 'nilai' => 'Nomor AHU-0009842.AH.01.12.TAHUN 2019, Tanggal 24 Mei 2019'],
                    ['label' => 'NPWP', 'nilai' => '31.402.764.0-623.000'],
                    ['label' => 'STP Yayasan Nomor', 'nilai' => 'P2T/69/07.03/01/XI/2019'],
                ],
                'sejarah' => implode("\n\n", [
                    'Berawal dari niat suci dan cita-cita mulia berkumpul para Insan Indonesia bersatu untuk membentuk Organisasi Nirlaba pada tahun 2011 yang diawali oleh rasa keprihatinan masih adanya anak-anak usia sekolah tetapi tidak bisa sekolah dikarenakan tidak ada biaya, keprihatinan dengan kondisi fakir-miskin yang membutuhkan bantuan dan membangun generasi ber-akhlaqkul karimah.',
                    'Maka sudah seharusnya Ikut berpartisipasi aktif membantu pemerintah dalam menangani masalah sosial, kemanusiaan, keagamaan dengan memberikan bantuan biaya sekolah, santunan kepada kaum fakir-miskin dan membangun tempat mengaji serta wakaf al-Quran.',
                    'Yang kemudian berkembang membangun Panti Asuhan/ Lembaga Sosial Kesejahteran Anak dalam rangka mengefektifkan dalam pemberian bantuan dan pengasuhan. Membangun masjid untuk beribadah, mengaji & kegiatan keagamaan.',
                    'Yayasan Insan Indonesia Bersatu Malang (YASIBU) dibangun untuk turut memajukan kesejahteraan masyarakat dan mencerdaskan kehidupan.',
                ]),
            ],
            'visi_misi' => [
                'motto' => 'Mewujudkan Kebersamaan, Membangun Kemandirian',
                'visi' => 'Menjadi Yayasan Nirlaba guna mewujudkan Insan Indonesia Bersatu dalam kemandirian',
                'misi' => [
                    'Mendidik anak yatim dan dhuafa dengan system pendidikan asrama yang berkualitas agar menjadi manusia yang berakhlakul karimah, beraqidah kokoh kuat terhadap Allah SWT dan syariat-Nya.',
                    'Memberikan bimbingan keterampilan kepada umat maupun anak asuh agar mampu menunjang pencapaian prestasi akademik & non akademik.',
                    'Menyelenggarakan program-program untuk memberdayakan potensi umat maupun anak asuh agar memiliki keterampilan tinggi dan bermental entrepreneur.',
                ],
            ],
            'pengurus' => [
                'inti' => [
                    ['nama' => 'Agus Purwadi', 'jabatan' => 'Ketua'],
                    ['nama' => 'Kusnul Huda', 'jabatan' => 'Sekretaris'],
                    ['nama' => 'Syaiful Rijal Aziz', 'jabatan' => 'Bendahara'],
                ],
                'bidang' => [
                    ['nama' => 'Bidang Pendidikan dan Pengembangan Anak', 'anggota' => ["Lulu Fati'ah", 'Rania Azzahra S.Pd', 'Ratu Cahaya Islami S.Pd', 'Syahra Salsabilla S.Pd']],
                    ['nama' => 'Bidang Donasi dan Usaha', 'anggota' => ['Ainul Yaqin', 'Al-Adiyat Atthur Robby', 'Hardi Agung', 'Imam Hanafi']],
                    ['nama' => 'Bidang Kesehatan', 'anggota' => ['Ibu Koyum']],
                    ['nama' => 'Bidang Hubungan Masyarakat', 'anggota' => ['Tony Misbah']],
                    ['nama' => 'Bidang Keamanan', 'anggota' => ['Purwanto']],
                    ['nama' => 'Bidang Pelayanan LKS', 'anggota' => ['Refmawati', 'Siti Romlah']],
                ],
                'pembina' => [
                    ['nama' => 'Eka Prasetyandani S. Pd. I', 'jabatan' => 'Pembina'],
                ],
            ],
            'kontak' => [
                'email' => 'sahabatyasibu@gmail.com',
                'whatsapp' => [
                    ['nomor' => '+62881036667747', 'nama' => 'Ustadzah Lulu'],
                    ['nomor' => '+6285791336429', 'nama' => 'Ustadzah Ratu'],
                ],
                'lokasi_nama' => 'Panti Asuhan Yasibu 2 (Suhat)',
                'lokasi_alamat' => "Jl. Kembang Kertas No.09, RT.09/RW.004, Jatimulyo,\nKec. Lowokwaru, Kota Malang, Jawa Timur 65141",
                'peta' => 'Jl. Kembang Kertas No.09, Jatimulyo, Lowokwaru, Kota Malang',
                'alamat_footer' => "Jalan Babatan III RT.02/RW.03 Kel,\nArjowinangun, Kec. Kedungkandang,\nKota Malang, Jawa Timur 65132",
            ],
            'sosial' => array_fill_keys(array_keys(self::MEDIA_SOSIAL), null),
        ];
    }
}
