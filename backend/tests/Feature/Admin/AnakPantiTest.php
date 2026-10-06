<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use App\Models\AnakPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnakPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.anak-panti.index'))->assertNotFound();
    }

    public function test_index_filters_by_status_and_name_or_nik(): void
    {
        AnakPanti::factory()->create(['Nama' => 'RAKA PRATAMA', 'NIK' => '3573000000000001']);
        AnakPanti::factory()->create(['Nama' => 'SITI AISYAH']);
        AnakPanti::factory()->alumni()->create(['Nama' => 'BUDI ALUMNI']);
        $admin = User::factory()->create();

        $alumni = $this->actingAs($admin)->get(route('admin.anak-panti.index', ['status' => StatusAsuh::Alumni->value]));
        $alumni->assertOk();
        $this->assertSame(['BUDI ALUMNI'], $alumni->viewData('anak')->pluck('Nama')->all());
        $this->assertSame(2, $alumni->viewData('jumlahAktif'));

        $nik = $this->get(route('admin.anak-panti.index', ['search' => '0000000001']));
        $this->assertSame(['RAKA PRATAMA'], $nik->viewData('anak')->pluck('Nama')->all());
    }

    public function test_admin_can_add_a_child(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.store'), $this->dataAnak())
            ->assertRedirect(route('admin.anak-panti.index', ['search' => '3573010101100001']))
            ->assertSessionHas('status', 'Data NABILA PUTRI berhasil ditambahkan.');

        $anak = AnakPanti::find('3573010101100001');
        $this->assertSame(JenisKelamin::Perempuan, $anak->Jenis_Kelamin);
        $this->assertSame('2014-05-20', $anak->Tanggal_Lahir->toDateString());
    }

    public function test_adding_a_child_rejects_duplicate_nik_and_future_birth_date(): void
    {
        AnakPanti::factory()->create(['NIK' => '3573010101100001']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.anak-panti.store'), $this->dataAnak(['Tanggal_Lahir' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors([
                'NIK' => 'NIK ini sudah terdaftar.',
                'Tanggal_Lahir' => 'Tanggal lahir tidak boleh setelah hari ini.',
            ]);

        $this->assertSame(1, AnakPanti::count());
    }

    public function test_admin_can_update_a_child_keeping_the_same_nik(): void
    {
        $anak = AnakPanti::factory()->create(['NIK' => '3573010101100001']);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.anak-panti.index'))
            ->put(route('admin.anak-panti.update', $anak), $this->dataAnak(['Status_Asuh' => StatusAsuh::Alumni->value, 'Kesehatan' => 'Asma']))
            ->assertRedirect(route('admin.anak-panti.index'))
            ->assertSessionHasNoErrors();

        $anak->refresh();
        $this->assertSame('NABILA PUTRI', $anak->Nama);
        $this->assertSame(StatusAsuh::Alumni, $anak->Status_Asuh);
        $this->assertSame('Asma', $anak->Kesehatan);
    }

    public function test_only_alumni_can_be_deleted(): void
    {
        $aktif = AnakPanti::factory()->create(['Nama' => 'RAKA']);
        $alumni = AnakPanti::factory()->alumni()->create();
        $this->actingAs(User::factory()->admin()->create())->from(route('admin.anak-panti.index'));

        $this->delete(route('admin.anak-panti.destroy', $aktif))
            ->assertSessionHasErrors(['hapus' => 'RAKA masih aktif, jadi tidak dapat dihapus. Ubah status asuh menjadi Alumni terlebih dulu.']);
        $this->delete(route('admin.anak-panti.destroy', $alumni))->assertSessionHasNoErrors();

        $this->assertModelExists($aktif);
        $this->assertModelMissing($alumni);
    }

    public function test_export_csv_contains_selected_children_and_columns_in_list_order(): void
    {
        $raka = AnakPanti::factory()->create(['Nama' => 'RAKA', 'Kesehatan' => 'Sehat']);
        AnakPanti::factory()->create(['Nama' => 'SITI']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.anak-panti.ekspor', [
            'format' => 'csv',
            'cakupan' => 'dipilih',
            'kolom' => ['kesehatan', 'nama'],
            'nik' => [$raka->NIK],
        ]));

        $response->assertOk()->assertDownload('data-anak-panti-'.now()->format('Y-m-d').'.csv');
        $isi = $response->baseResponse->getFile()->getContent();
        // BOM UTF-8 di awal supaya Excel membaca huruf non-ASCII dengan benar.
        $this->assertStringStartsWith("\u{FEFF}", $isi);
        $baris = array_map(str_getcsv(...), array_filter(explode("\n", substr($isi, 3))));
        $this->assertSame([['Nama', 'Kesehatan'], ['RAKA', 'Sehat']], array_values($baris));
    }

    /**
     * @param  array<string, string>  $ubah
     * @return array<string, string>
     */
    private function dataAnak(array $ubah = []): array
    {
        return [
            'NIK' => '3573010101100001',
            'Nama' => 'NABILA PUTRI',
            'Jenis_Kelamin' => JenisKelamin::Perempuan->value,
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => '2014-05-20',
            'Agama' => 'Islam',
            'Keterangan' => 'Yatim',
            'Status_Anak' => 'Pelajar',
            'Pendidikan' => 'SD',
            'Status_Asuh' => StatusAsuh::MasihAktif->value,
            'Kesehatan' => 'Sehat',
            ...$ubah,
        ];
    }
}
