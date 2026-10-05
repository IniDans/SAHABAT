<?php

namespace Tests\Feature\Api;

use App\Models\Pengasuh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PengasuhTest extends TestCase
{
    use RefreshDatabase;

    public function test_caregiver_crud(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/pengasuh', [
            'NIK' => '3573051412770001',
            'Nama' => 'Agus Purwadi',
            'Jabatan' => 'Wakil Ketua Yayasan',
            'Jenis_Kelamin' => 'Laki-Laki',
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => '1977-12-14',
            'Agama' => 'Islam',
            'Email' => 'agus.purwadi@yayasan.org',
            'Pendidikan' => 'S1 Manajemen Pendidikan',
            'Nomor_Telepon' => '081234567890',
            'Alamat' => 'Jl. Bunga Kopi No. 15, Malang',
        ])->assertCreated()->assertJsonPath('data.Jabatan', 'Wakil Ketua Yayasan');

        $this->patchJson('/api/pengasuh/3573051412770001', ['Jabatan' => 'Ketua Yayasan'])
            ->assertOk()->assertJsonPath('data.Jabatan', 'Ketua Yayasan');

        $this->getJson('/api/pengasuh?Jabatan=Ketua Yayasan')
            ->assertOk()->assertJsonCount(1, 'data');

        $this->deleteJson('/api/pengasuh/3573051412770001')->assertNoContent();
        $this->assertDatabaseMissing('pengasuh', ['NIK' => '3573051412770001']);
    }

    public function test_nik_must_be_unique(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $pengasuh = Pengasuh::factory()->create();

        $this->postJson('/api/pengasuh', ['NIK' => $pengasuh->NIK])
            ->assertUnprocessable()->assertJsonValidationErrors('NIK');

        $this->patchJson("/api/pengasuh/{$pengasuh->NIK}", ['NIK' => $pengasuh->NIK])
            ->assertOk();
    }
}
