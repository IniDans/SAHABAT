<?php

namespace Tests\Feature\Api;

use App\Models\AnakPanti;
use App\Models\User;
use App\Models\WaliAnak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnakPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_can_register_a_child_with_database_defaults(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $wali = WaliAnak::factory()->create(['Nama_Wali' => 'Siti Fatimah']);

        $this->postJson('/api/anak-panti', [
            'NIK' => '3573050103070001',
            'Nama' => 'AHMAD FAUZI',
            'Jenis_Kelamin' => 'Laki-Laki',
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => '2015-03-10',
            'Agama' => 'Islam',
            'Status_Anak' => 'Pelajar',
            'ID_Wali' => $wali->ID_Wali,
            'Pendidikan' => 'Sekolah',
        ])->assertCreated()
            ->assertJsonPath('data.NIK', '3573050103070001')
            ->assertJsonPath('data.Keterangan', 'Dhuafa')
            ->assertJsonPath('data.Status_Asuh', 'Masih Aktif')
            ->assertJsonPath('data.wali.Nama_Wali', 'Siti Fatimah');

        $this->getJson('/api/anak-panti/3573050103070001')
            ->assertOk()->assertJsonPath('data.Tanggal_Lahir', '2015-03-10');
    }

    public function test_validation_errors_are_returned(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $existing = AnakPanti::factory()->create();

        $this->postJson('/api/anak-panti', [
            'NIK' => $existing->NIK,
            'Jenis_Kelamin' => 'L',
            'ID_Wali' => 999,
            'Status_Asuh' => 'Keluar',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'NIK', 'Nama', 'Jenis_Kelamin', 'Tempat_Lahir', 'Agama',
                'Status_Anak', 'ID_Wali', 'Pendidikan', 'Status_Asuh',
            ]);
    }

    public function test_listing_can_be_filtered_by_status_and_searched(): void
    {
        Sanctum::actingAs(User::factory()->create());
        AnakPanti::factory()->create(['Nama' => 'BAYU RIAN']);
        AnakPanti::factory()->alumni()->create(['Nama' => 'BAYU ALUMNI']);
        AnakPanti::factory()->create(['Nama' => 'SITI']);

        $this->getJson('/api/anak-panti?search=bayu&Status_Asuh=Masih Aktif')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.Nama', 'BAYU RIAN');
    }

    public function test_child_can_be_updated_and_marked_as_alumni(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $anak = AnakPanti::factory()->create();

        $this->patchJson("/api/anak-panti/{$anak->NIK}", ['Status_Asuh' => 'Alumni', 'Pendidikan' => 'Sudah Lulus'])
            ->assertOk()
            ->assertJsonPath('data.Status_Asuh', 'Alumni')
            ->assertJsonPath('data.Pendidikan', 'Sudah Lulus');
    }

    public function test_active_child_cannot_be_deleted(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $anak = AnakPanti::factory()->create();

        $this->deleteJson("/api/anak-panti/{$anak->NIK}")->assertConflict();

        $this->assertModelExists($anak);
    }

    public function test_admin_can_delete_an_alumni(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $anak = AnakPanti::factory()->alumni()->create();

        $this->deleteJson("/api/anak-panti/{$anak->NIK}")->assertNoContent();

        $this->assertModelMissing($anak);
    }

    public function test_pengurus_cannot_delete_a_child(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $anak = AnakPanti::factory()->alumni()->create();

        $this->deleteJson("/api/anak-panti/{$anak->NIK}")->assertForbidden();
    }
}
