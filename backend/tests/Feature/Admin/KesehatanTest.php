<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisKelamin;
use App\Enums\StatusGizi;
use App\Models\AnakPanti;
use App\Models\BeratBadan;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KesehatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-15');
    }

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.kesehatan.index'))->assertNotFound();
        $this->get(route('admin.kesehatan.timbang'))->assertNotFound();
        $this->put(route('admin.kesehatan.simpan'))->assertNotFound();
    }

    public function test_screening_compares_weight_change_and_standard_for_age(): void
    {
        // Laki-laki 9 tahun: median 28,1 kg. Perempuan 7 tahun: median 22,4 kg.
        $turun = $this->anak('RAKA', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 24.1, '2026-10' => 23.4]);
        $kurus = $this->anak('DIMAS', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 21.8, '2026-10' => 22.0]);
        $tetap = $this->anak('SITI', JenisKelamin::Perempuan, '2019-10-01', ['2026-09' => 21.0, '2026-10' => 21.0]);
        $sakit = $this->anak('NABILA', JenisKelamin::Perempuan, '2019-10-01', ['2026-09' => 22.0, '2026-10' => 22.3], 'Asma');
        $baik = $this->anak('BUDI', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 27.6, '2026-10' => 28.0]);
        $belum = $this->anak('ANI', JenisKelamin::Perempuan, '2019-10-01', ['2026-09' => 22.0]);
        $alumni = AnakPanti::factory()->alumni()->create();
        BeratBadan::factory()->for($alumni, 'anak')->create(['bulan' => '2026-10-01']);

        $hasil = AnakPanti::skriningGizi(CarbonImmutable::parse('2026-10-01'))->keyBy(fn (array $item): string => $item['anak']->NIK);

        $this->assertCount(6, $hasil);
        $this->assertSame(StatusGizi::Kurang, $hasil[$turun->NIK]['status']);
        $this->assertSame(-0.7, $hasil[$turun->NIK]['perubahan']);
        $this->assertSame(StatusGizi::Kurang, $hasil[$kurus->NIK]['status']);
        $this->assertSame(StatusGizi::Dipantau, $hasil[$tetap->NIK]['status']);
        $this->assertSame('Tambah asupan protein, timbang ulang', $hasil[$tetap->NIK]['saran']);
        $this->assertSame(StatusGizi::Dipantau, $hasil[$sakit->NIK]['status']);
        $this->assertSame('Ikuti catatan kesehatan: Asma', $hasil[$sakit->NIK]['saran']);
        $this->assertSame(StatusGizi::Baik, $hasil[$baik->NIK]['status']);
        $this->assertSame(9, $hasil[$baik->NIK]['umur']);
        $this->assertSame(StatusGizi::BelumDitimbang, $hasil[$belum->NIK]['status']);
    }

    public function test_dashboard_summarizes_health_and_lists_children_needing_attention(): void
    {
        $this->anak('RAKA PRATAMA', JenisKelamin::LakiLaki, '2017-10-01', ['2026-08' => 24.0, '2026-09' => 24.2, '2026-10' => 23.4]);
        $this->anak('BUDI', JenisKelamin::LakiLaki, '2017-10-01', ['2026-08' => 26.0, '2026-09' => 27.0, '2026-10' => 28.0]);
        $this->anak('ANI', JenisKelamin::Perempuan, '2019-10-01', []);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Pantau kesehatan anak panti')
            ->assertSee('Rata-rata berat badan naik 0,1 kg dari bulan lalu.')
            ->assertSee('1 anak perlu dipantau lebih dekat bulan ini.')
            ->assertSee('1 anak belum ditimbang pada Oktober 2026.')
            ->assertSee('Lihat semua 1 anak');

        $kesehatan = $response->viewData('kesehatan');
        $this->assertSame(['RAKA PRATAMA'], $kesehatan['perhatian']->pluck('anak.Nama')->all());
        $this->assertSame(1, $kesehatan['jumlahPerStatus'][StatusGizi::Baik->value]);
        $this->assertSame([null, null, null, 25.0, 25.6, 25.7], $kesehatan['tren']['titik']->pluck('rata')->all());
        $this->assertSame('2026-11-01', $kesehatan['tren']['prakiraan']['bulan']->toDateString());
        $this->assertSame(26.1, $kesehatan['tren']['prakiraan']['nilai']);
    }

    public function test_dashboard_chart_can_follow_one_child(): void
    {
        $budi = $this->anak('BUDI', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 27.0, '2026-10' => 28.0]);
        $this->anak('RAKA', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 20.0, '2026-10' => 20.0]);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard', ['anak' => $budi->NIK]));

        $response->assertOk()->assertSee('Tren berat badan BUDI');
        $this->assertSame([27.0, 28.0], $response->viewData('kesehatan')['tren']['titik']->pluck('rata')->filter()->values()->all());
    }

    public function test_dashboard_without_weights_invites_first_input(): void
    {
        $this->anak('ANI', JenisKelamin::Perempuan, '2019-10-01', []);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada data berat badan. Mulai dengan input berat badan bulan ini.')
            ->assertSee('Belum ada data berat badan untuk grafik ini.');
    }

    public function test_index_filters_by_status_and_name(): void
    {
        $this->anak('RAKA', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 24.1, '2026-10' => 23.4]);
        $this->anak('BUDI', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 27.6, '2026-10' => 28.0]);
        $this->anak('BUDIMAN', JenisKelamin::LakiLaki, '2017-10-01', []);
        $admin = User::factory()->create();

        $perhatian = $this->actingAs($admin)->get(route('admin.kesehatan.index', ['status' => 'perhatian']));
        $perhatian->assertOk()->assertSee('Hasil skrining Oktober 2026');
        $this->assertSame(['RAKA'], $perhatian->viewData('daftar')->pluck('anak.Nama')->all());

        $cari = $this->get(route('admin.kesehatan.index', ['search' => 'budi']));
        $this->assertSame(['BUDIMAN', 'BUDI'], $cari->viewData('daftar')->pluck('anak.Nama')->all());

        $bulanLalu = $this->get(route('admin.kesehatan.index', ['bulan' => '2026-09', 'status' => StatusGizi::BelumDitimbang->value]));
        $this->assertSame(['BUDIMAN'], $bulanLalu->viewData('daftar')->pluck('anak.Nama')->all());
    }

    public function test_weighing_form_shows_last_month_and_current_values(): void
    {
        $raka = $this->anak('RAKA', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 24.1, '2026-10' => 23.4]);
        AnakPanti::factory()->alumni()->create(['Nama' => 'ALUMNI']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kesehatan.timbang'))
            ->assertOk()
            ->assertSee('Penimbangan Oktober 2026')
            ->assertSee('24,1 kg')
            ->assertSee('name="berat['.$raka->NIK.']" value="23,4"', false)
            ->assertDontSee('ALUMNI');
    }

    public function test_saving_weights_creates_updates_and_clears_records(): void
    {
        $baru = $this->anak('BARU', JenisKelamin::LakiLaki, '2017-10-01', []);
        $ubah = $this->anak('UBAH', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 25.0, '2026-10' => 25.5]);
        $hapus = $this->anak('HAPUS', JenisKelamin::LakiLaki, '2017-10-01', ['2026-09' => 25.0, '2026-10' => 25.5]);
        $alumni = AnakPanti::factory()->alumni()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.kesehatan.simpan'), [
                'bulan' => '2026-10',
                'berat' => [$baru->NIK => '23,4', $ubah->NIK => '26', $hapus->NIK => '', $alumni->NIK => '30'],
            ])
            ->assertRedirect(route('admin.kesehatan.timbang', ['bulan' => '2026-10']))
            ->assertSessionHas('status', 'Berat badan 2 anak untuk Oktober 2026 disimpan.');

        $oktober = BeratBadan::query()->padaBulan(CarbonImmutable::parse('2026-10-01'))->pluck('berat_kg', 'nik');
        $this->assertEquals([$baru->NIK => 23.4, $ubah->NIK => 26.0], $oktober->all());
        $this->assertSame(2, BeratBadan::query()->padaBulan(CarbonImmutable::parse('2026-09-01'))->count());
    }

    public function test_saving_weights_rejects_unrealistic_values_and_future_months(): void
    {
        $anak = $this->anak('RAKA', JenisKelamin::LakiLaki, '2017-10-01', []);
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.kesehatan.simpan'), ['bulan' => '2026-10', 'berat' => [$anak->NIK => '1,5']])
            ->assertSessionHasErrors(['berat.'.$anak->NIK => 'Berat harus antara 2 dan 150 kg.']);

        $this->put(route('admin.kesehatan.simpan'), ['bulan' => '2026-11', 'berat' => [$anak->NIK => '25']])
            ->assertSessionHasErrors(['bulan' => 'Bulan penimbangan tidak boleh setelah bulan ini.']);

        $this->assertSame(0, BeratBadan::count());
    }

    /**
     * Anak aktif dengan berat per bulan, mis. ['2026-10' => 23.4].
     *
     * @param  array<string, float>  $berat
     */
    private function anak(string $nama, JenisKelamin $jenisKelamin, string $tanggalLahir, array $berat, string $kesehatan = 'Sehat'): AnakPanti
    {
        $anak = AnakPanti::factory()->create([
            'Nama' => $nama,
            'Jenis_Kelamin' => $jenisKelamin,
            'Tanggal_Lahir' => $tanggalLahir,
            'Kesehatan' => $kesehatan,
        ]);

        foreach ($berat as $bulan => $kg) {
            BeratBadan::factory()->for($anak, 'anak')->create(['bulan' => "{$bulan}-01", 'berat_kg' => $kg]);
        }

        return $anak;
    }
}
