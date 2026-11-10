<?php

namespace Tests\Feature\Admin;

use App\Models\AnakPanti;
use App\Models\LogAktivitas;
use App\Models\User;
use App\Services\CadanganDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CadanganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    public function test_command_writes_a_gzipped_sql_dump_and_prunes_old_ones(): void
    {
        AnakPanti::factory()->create(['Nama' => "Siti Nur'aini"]);
        Storage::put('cadangan/cadangan-20260101-020000.sql.gz', 'lama');
        Storage::put('cadangan/cadangan-20260102-020000.sql.gz', 'lama');

        $this->travelTo('2026-11-11 02:00:00');
        $this->artisan('cadangan:buat', ['--simpan' => 2])->assertSuccessful();

        $this->assertSame([
            'cadangan/cadangan-20260102-020000.sql.gz',
            'cadangan/cadangan-20261111-020000.sql.gz',
        ], Storage::files('cadangan'));

        $sql = gzdecode(Storage::get('cadangan/cadangan-20261111-020000.sql.gz'));
        $this->assertStringContainsString('INSERT INTO', $sql);
        $this->assertStringContainsString("Siti Nur''aini", $sql);
        $this->assertStringNotContainsString('"sessions"', $sql);
    }

    public function test_only_admin_can_open_the_backup_page(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.cadangan.index'))->assertForbidden();
        $this->post(route('admin.cadangan.store'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())->get(route('admin.cadangan.index'))->assertOk();
    }

    public function test_admin_can_create_and_download_a_backup(): void
    {
        $this->travelTo('2026-11-11 09:30:00');
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('admin.cadangan.store'))->assertRedirect(route('admin.cadangan.index'));
        Storage::assertExists('cadangan/cadangan-20261111-093000.sql.gz');

        $this->get(route('admin.cadangan.index'))->assertSee('cadangan-20261111-093000.sql.gz');
        $this->get(route('admin.cadangan.unduh', 'cadangan-20261111-093000.sql.gz'))->assertDownload('cadangan-20261111-093000.sql.gz');

        $this->assertSame([LogAktivitas::AKSI_TAMBAH, LogAktivitas::AKSI_UNDUH], LogAktivitas::orderBy('id')->pluck('aksi')->all());
    }

    public function test_download_rejects_names_outside_the_backup_pattern(): void
    {
        Storage::put('rahasia.txt', 'isi');
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.cadangan.unduh', 'rahasia.txt'))->assertNotFound();
        $this->get(route('admin.cadangan.unduh', '..%2Frahasia.txt'))->assertNotFound();
        $this->get(route('admin.cadangan.unduh', 'cadangan-20260101-000000.sql.gz'))->assertNotFound();
        $this->assertFalse(CadanganDatabase::namaValid('../cadangan-20260101-000000.sql.gz'));
    }
}
