<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisKelamin;
use App\Models\AnakPanti;
use App\Models\User;
use App\Models\WaliAnak;
use App\Services\PembacaDokumenAnak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class PindaiDokumenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-30');
    }

    public function test_guests_get_not_found(): void
    {
        $this->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->image('kk.jpg')])->assertNotFound();
    }

    public function test_scanned_family_card_is_prepared_for_review(): void
    {
        AnakPanti::factory()->create(['NIK' => '3573010101150002']);
        $this->fakeAi($this->hasilAi());

        $this->actingAs(User::factory()->create())
            ->from(route('admin.anak-panti.index'))
            ->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->image('kk.jpg')])
            ->assertRedirect(route('admin.anak-panti.pindai'));

        $response = $this->get(route('admin.anak-panti.pindai'))->assertOk()->assertSee('Kartu Keluarga');
        $hasil = $response->viewData('hasil');

        // Ayah, anak 10 tahun, anak yang sudah terdaftar, anak dewasa.
        $this->assertSame([false, true, false, false], array_column($hasil['anggota'], 'pilih'));
        $this->assertTrue($hasil['anggota'][2]['terdaftar']);
        $this->assertSame([
            'NIK' => '3573010101160001',
            'Nama' => 'NABILA PUTRI',
            'Jenis_Kelamin' => JenisKelamin::Perempuan->value,
            'Tempat_Lahir' => 'MALANG',
            'Tanggal_Lahir' => '2016-05-20',
            'Agama' => 'Islam',
            'Keterangan' => 'Dhuafa',
            'Status_Anak' => 'Pelajar',
            'Pendidikan' => 'SD',
            'Status_Asuh' => 'Masih Aktif',
            'Kesehatan' => 'Sehat',
        ], $hasil['anggota'][1]['data']);
        $this->assertSame(['pilihan' => 'baru', 'Nama_Wali' => 'AHMAD SUBARI', 'Alamat_Wali' => 'JL. MAWAR NO. 5 RT 001/RW 002, LOWOKWARU, MALANG'], $hasil['wali']);
    }

    public function test_scan_suggests_the_registered_guardian_with_the_same_name(): void
    {
        $wali = WaliAnak::factory()->create(['Nama_Wali' => 'Ahmad Subari']);
        $this->fakeAi($this->hasilAi());

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->image('kk.jpg')]);

        $this->assertSame((string) $wali->ID_Wali, session('pindai_anak.wali.pilihan'));
    }

    public function test_scan_reports_documents_that_cannot_be_read(): void
    {
        $this->partialMock(PembacaDokumenAnak::class, function (MockInterface $mock) {
            $mock->shouldReceive('baca')->once()->andThrow(new RuntimeException('Layanan AI sedang tidak dapat dihubungi.'));
        });

        $this->actingAs(User::factory()->create())
            ->from(route('admin.anak-panti.index'))
            ->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->image('kk.jpg')])
            ->assertRedirect(route('admin.anak-panti.index'))
            ->assertSessionHasErrors(['dokumen' => 'Dokumen belum terbaca: Layanan AI sedang tidak dapat dihubungi.']);

        $this->assertNull(session('pindai_anak'));
    }

    public function test_scan_rejects_documents_that_are_not_a_family_card_or_birth_certificate(): void
    {
        $this->fakeAi($this->hasilAi(['jenis_dokumen' => 'lainnya', 'anggota' => [], 'catatan' => 'Gambar berisi struk belanja.']));

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->image('struk.jpg')])
            ->assertSessionHasErrors(['dokumen' => 'Dokumen tidak terbaca sebagai KK atau akta kelahiran. Gambar berisi struk belanja.']);
    }

    public function test_scan_only_accepts_photos_and_pdf(): void
    {
        $this->mock(PembacaDokumenAnak::class, fn (MockInterface $mock) => $mock->shouldNotReceive('baca'));

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.baca'), ['dokumen' => UploadedFile::fake()->create('kk.docx', 10)])
            ->assertSessionHasErrors(['dokumen' => 'Dokumen harus berformat JPG, PNG, WEBP, atau PDF.']);
    }

    public function test_review_page_without_a_scan_goes_back_to_the_list(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.anak-panti.pindai'))
            ->assertRedirect(route('admin.anak-panti.index'));
    }

    public function test_saving_a_scan_adds_only_checked_children_with_a_new_guardian(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['pindai_anak' => ['jenis_dokumen' => 'kk']])
            ->post(route('admin.anak-panti.pindai.simpan'), [
                'wali' => 'baru',
                'Nama_Wali' => 'AHMAD SUBARI',
                'Alamat_Wali' => 'JL. MAWAR NO. 5, MALANG',
                'anak' => [
                    0 => $this->barisAnak(['NIK' => '3573010101800001', 'Nama' => 'AHMAD SUBARI']),
                    1 => $this->barisAnak(['pilih' => '1']),
                ],
            ])
            ->assertRedirect(route('admin.anak-panti.index', ['search' => '3573010101160001']))
            ->assertSessionHas('status', 'Data NABILA PUTRI berhasil ditambahkan dari dokumen.')
            ->assertSessionMissing('pindai_anak');

        $anak = AnakPanti::sole();
        $this->assertSame('3573010101160001', $anak->NIK);
        $this->assertSame('AHMAD SUBARI', $anak->wali->Nama_Wali);
        $this->assertSame('JL. MAWAR NO. 5, MALANG', $anak->wali->Alamat_Wali);
    }

    public function test_saving_a_scan_can_use_a_registered_guardian(): void
    {
        $wali = WaliAnak::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.simpan'), [
                'wali' => (string) $wali->ID_Wali,
                'anak' => [$this->barisAnak(['pilih' => '1'])],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($wali->ID_Wali, AnakPanti::sole()->ID_Wali);
        $this->assertSame(1, WaliAnak::count());
    }

    public function test_saving_a_scan_validates_each_checked_child(): void
    {
        AnakPanti::factory()->create(['NIK' => '3573010101160001']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.simpan'), [
                'wali' => 'baru',
                'anak' => [
                    3 => $this->barisAnak(['pilih' => '1', 'Pendidikan' => '']),
                ],
            ])
            ->assertSessionHasErrors([
                'anak.3.NIK' => 'NIK ini sudah terdaftar.',
                'anak.3.Pendidikan' => 'Pendidikan wajib diisi.',
                'Nama_Wali' => 'Nama wali wajib diisi.',
            ]);

        $this->assertSame(1, AnakPanti::count());
    }

    public function test_saving_a_scan_requires_at_least_one_checked_child(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.pindai.simpan'), ['anak' => [$this->barisAnak()]])
            ->assertSessionHasErrors(['anak' => 'Centang minimal satu anak yang akan disimpan.']);

        $this->assertSame(0, AnakPanti::count());
    }

    public function test_cancelling_a_scan_forgets_the_result(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['pindai_anak' => ['jenis_dokumen' => 'kk']])
            ->delete(route('admin.anak-panti.pindai.batal'))
            ->assertRedirect(route('admin.anak-panti.index'))
            ->assertSessionMissing('pindai_anak');
    }

    /**
     * Ganti pemanggilan Gemini dengan hasil tetap; penyiapan form tetap memakai kode asli.
     *
     * @param  array<string, mixed>  $hasil
     */
    private function fakeAi(array $hasil): void
    {
        $this->partialMock(PembacaDokumenAnak::class, function (MockInterface $mock) use ($hasil) {
            $mock->shouldReceive('baca')->once()->andReturn($hasil);
        });
    }

    /**
     * Hasil baca KK: ayah, anak 10 tahun, anak yang sudah terdaftar, dan anak dewasa.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function hasilAi(array $overrides = []): array
    {
        $orang = fn (string $nik, string $nama, string $jenisKelamin, string $tanggalLahir, string $hubungan): array => [
            'nik' => $nik,
            'nama' => $nama,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => 'MALANG',
            'tanggal_lahir' => $tanggalLahir,
            'agama' => 'ISLAM',
            'pendidikan' => 'SLTA/SEDERAJAT',
            'hubungan' => $hubungan,
            'nama_ayah' => null,
            'nama_ibu' => null,
        ];

        return [
            'jenis_dokumen' => 'kk',
            'nomor_dokumen' => '3573010101010001',
            'kepala_keluarga' => 'AHMAD SUBARI',
            'alamat' => 'JL. MAWAR NO. 5 RT 001/RW 002, LOWOKWARU, MALANG',
            'anggota' => [
                $orang('3573010101800001', 'AHMAD SUBARI', 'LAKI-LAKI', '1980-01-01', 'KEPALA KELUARGA'),
                $orang('3573 0101 0116 0001', 'NABILA PUTRI', 'PEREMPUAN', '2016-05-20', 'ANAK'),
                $orang('3573010101150002', 'RAKA PRATAMA', 'LAKI-LAKI', '2015-02-11', 'ANAK'),
                $orang('3573010101000003', 'DIMAS SAPUTRA', 'LAKI-LAKI', '2000-08-17', 'ANAK'),
            ],
            'catatan' => null,
            ...$overrides,
        ];
    }

    /**
     * Satu baris form tinjauan seperti yang dikirim halaman hasil scan.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function barisAnak(array $overrides = []): array
    {
        return [
            'NIK' => '3573010101160001',
            'Nama' => 'NABILA PUTRI',
            'Jenis_Kelamin' => JenisKelamin::Perempuan->value,
            'Tempat_Lahir' => 'MALANG',
            'Tanggal_Lahir' => '2016-05-20',
            'Agama' => 'Islam',
            'Keterangan' => 'Dhuafa',
            'Status_Anak' => 'Pelajar',
            'Pendidikan' => 'SD',
            'Status_Asuh' => 'Masih Aktif',
            'Kesehatan' => 'Sehat',
            ...$overrides,
        ];
    }
}
