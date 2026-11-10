<?php

namespace Tests\Feature\Admin;

use App\Enums\KategoriKebutuhan;
use App\Enums\MetodePembayaran;
use App\Enums\PrioritasKebutuhan;
use App\Enums\ProgramDonasi;
use App\Enums\TampilanDonatur;
use App\Models\KebutuhanPanti;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogAktivitasTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_update_and_delete_by_staff_are_logged(): void
    {
        $user = User::factory()->admin()->create(['name' => 'Bu Sari']);
        $this->actingAs($user);

        $this->post(route('admin.kebutuhan-panti.store'), $this->kebutuhan(['target_dana' => 'Rp1.500.000']));
        $kebutuhan = KebutuhanPanti::sole();
        $this->assertSame(1500000, $kebutuhan->target_dana);

        $this->put(route('admin.kebutuhan-panti.update', $kebutuhan), $this->kebutuhan(['jumlah' => 60, 'target_dana' => '1500000']));
        $this->delete(route('admin.kebutuhan-panti.destroy', $kebutuhan));

        $log = LogAktivitas::orderBy('id')->get();
        $this->assertSame([LogAktivitas::AKSI_TAMBAH, LogAktivitas::AKSI_UBAH, LogAktivitas::AKSI_HAPUS], $log->pluck('aksi')->all());
        $this->assertSame(['jumlah'], $log[1]->kolom);
        $this->assertSame('Bu Sari', $log[0]->nama_pengguna);
        $this->assertSame('KebutuhanPanti', $log[0]->subjek_tipe);
    }

    public function test_public_submissions_are_not_logged(): void
    {
        $this->post(route('donasi.formulir.store'), [
            'program' => ProgramDonasi::Infak->value,
            'nominal' => 'Rp50.000',
            'nama_donatur' => 'Ibu Ratna',
            'tampil_sebagai' => TampilanDonatur::NamaAsli->value,
            'no_whatsapp' => '85100767634',
            'email' => 'ratna@example.com',
            'metode_pembayaran' => MetodePembayaran::Bca->value,
        ]);

        $this->assertDatabaseCount('donasi', 1);
        $this->assertDatabaseCount('log_aktivitas', 0);
    }

    public function test_login_is_logged(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['login' => $user->email, 'password' => 'password']);

        $this->assertSame(LogAktivitas::AKSI_MASUK, LogAktivitas::sole()->aksi);
    }

    public function test_only_admin_can_view_the_log_and_filter_it(): void
    {
        $sari = User::factory()->create(['name' => 'Bu Sari']);
        LogAktivitas::factory()->for($sari)->create(['subjek_label' => 'Beras', 'aksi' => LogAktivitas::AKSI_HAPUS]);
        LogAktivitas::factory()->create(['subjek_label' => 'Seragam sekolah', 'aksi' => LogAktivitas::AKSI_UBAH]);

        $this->actingAs($sari)->get(route('admin.log-aktivitas.index'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.log-aktivitas.index', ['aksi' => LogAktivitas::AKSI_HAPUS]))
            ->assertOk()
            ->assertSee('Beras')
            ->assertDontSee('Seragam sekolah');

        $this->get(route('admin.log-aktivitas.index', ['user' => $sari->id, 'search' => 'Seragam']))
            ->assertDontSee('Beras');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function kebutuhan(array $overrides = []): array
    {
        return [
            'nama' => 'Beras',
            'kategori' => KategoriKebutuhan::Pangan->value,
            'jumlah' => 50,
            'satuan' => 'kg',
            'prioritas' => PrioritasKebutuhan::Mendesak->value,
            'skor_prioritas' => 95,
            ...$overrides,
        ];
    }
}
