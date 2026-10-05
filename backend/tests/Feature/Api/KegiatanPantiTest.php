<?php

namespace Tests\Feature\Api;

use App\Models\KegiatanPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KegiatanPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_is_created_with_the_default_photo(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/kegiatan-panti', [
            'nama_kegiatan' => 'Bakti Sosial Ramadhan',
            'jenis_kegiatan' => 'Sosial',
            'tanggal_kegiatan' => '2026-03-20',
        ])->assertCreated()
            ->assertJsonPath('data.nama_foto', 'default_kegiatan.jpg')
            ->assertJsonPath('data.foto_url', null);

        $this->postJson('/api/kegiatan-panti', ['jenis_kegiatan' => 'Rapat'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nama_kegiatan', 'jenis_kegiatan', 'tanggal_kegiatan']);
    }

    public function test_listing_can_be_filtered_by_type_and_date(): void
    {
        Sanctum::actingAs(User::factory()->create());
        KegiatanPanti::factory()->create(['jenis_kegiatan' => 'Ibadah', 'tanggal_kegiatan' => '2026-03-01']);
        KegiatanPanti::factory()->create(['jenis_kegiatan' => 'Ibadah', 'tanggal_kegiatan' => '2026-05-01']);
        KegiatanPanti::factory()->create(['jenis_kegiatan' => 'Olahraga', 'tanggal_kegiatan' => '2026-05-01']);

        $this->getJson('/api/kegiatan-panti?jenis_kegiatan=Ibadah&dari=2026-04-01')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tanggal_kegiatan', '2026-05-01');
    }

    public function test_photo_can_be_uploaded_replaced_and_viewed(): void
    {
        Storage::fake();
        Sanctum::actingAs(User::factory()->create());
        $kegiatan = KegiatanPanti::factory()->create();

        $first = $this->post("/api/kegiatan-panti/{$kegiatan->id_kegiatan}/foto", [
            'foto' => UploadedFile::fake()->image('baksos.jpg'),
        ])->assertOk()->json('data.nama_foto');

        Storage::assertExists("kegiatan/{$first}");

        $this->post("/api/kegiatan-panti/{$kegiatan->id_kegiatan}/foto", [
            'foto' => UploadedFile::fake()->image('baksos-2.jpg'),
        ])->assertOk();

        Storage::assertMissing("kegiatan/{$first}");
        $this->get("/api/kegiatan-panti/{$kegiatan->id_kegiatan}/foto")->assertOk();
    }
}
