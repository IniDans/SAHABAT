<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisKegiatan;
use App\Models\KegiatanPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KegiatanPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.kegiatan.index'))->assertNotFound();
    }

    public function test_index_lists_newest_first_and_filters_by_type(): void
    {
        KegiatanPanti::factory()->create(['nama_kegiatan' => 'Pengajian rutin', 'jenis_kegiatan' => JenisKegiatan::Ibadah, 'tanggal_kegiatan' => '2026-09-01']);
        KegiatanPanti::factory()->create(['nama_kegiatan' => 'Lomba 17 Agustus', 'jenis_kegiatan' => JenisKegiatan::Olahraga, 'tanggal_kegiatan' => '2026-08-17']);
        KegiatanPanti::factory()->create(['nama_kegiatan' => 'Bakti sosial', 'jenis_kegiatan' => JenisKegiatan::Sosial, 'tanggal_kegiatan' => '2026-10-01']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kegiatan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Bakti sosial', 'Pengajian rutin', 'Lomba 17 Agustus']);

        $this->get(route('admin.kegiatan.index', ['jenis' => JenisKegiatan::Ibadah->value]))
            ->assertSee('Pengajian rutin')
            ->assertDontSee('Bakti sosial');
    }

    public function test_kegiatan_can_be_created_with_a_private_photo(): void
    {
        Storage::fake();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kegiatan.create'))
            ->assertOk();

        $this->post(route('admin.kegiatan.store'), $this->payload(['foto' => UploadedFile::fake()->image('kegiatan.jpg')]))
            ->assertRedirect(route('admin.kegiatan.index'))
            ->assertSessionHas('status');

        $kegiatan = KegiatanPanti::sole();
        $this->assertSame('Pengajian rutin', $kegiatan->nama_kegiatan);
        $this->assertSame(JenisKegiatan::Ibadah, $kegiatan->jenis_kegiatan);
        Storage::assertExists($kegiatan->fotoPath());

        $this->get(route('admin.kegiatan.foto', $kegiatan))->assertOk();
    }

    public function test_kegiatan_without_photo_keeps_the_default(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.kegiatan.store'), $this->payload())
            ->assertRedirect(route('admin.kegiatan.index'));

        $kegiatan = KegiatanPanti::sole();
        $this->assertSame(KegiatanPanti::FOTO_DEFAULT, $kegiatan->nama_foto);
        $this->get(route('admin.kegiatan.foto', $kegiatan))->assertNotFound();
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.kegiatan.store'), $this->payload(['foto' => UploadedFile::fake()->create('skrip.php', 10, 'application/x-php')]))
            ->assertSessionHasErrors('foto');

        $this->assertSame(0, KegiatanPanti::count());
        $this->assertSame([], Storage::allFiles());
    }

    public function test_replacing_the_photo_deletes_the_old_one(): void
    {
        Storage::fake();
        $kegiatan = KegiatanPanti::factory()->create(['nama_foto' => 'lama.jpg']);
        Storage::put('kegiatan/lama.jpg', 'foto lama');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kegiatan.edit', $kegiatan))
            ->assertOk();

        $this->put(route('admin.kegiatan.update', $kegiatan), $this->payload(['foto' => UploadedFile::fake()->image('baru.png')]))
            ->assertRedirect(route('admin.kegiatan.index'));

        $kegiatan->refresh();
        $this->assertNotSame('lama.jpg', $kegiatan->nama_foto);
        Storage::assertMissing('kegiatan/lama.jpg');
        Storage::assertExists($kegiatan->fotoPath());
    }

    public function test_only_admin_can_delete_and_the_photo_is_removed(): void
    {
        Storage::fake();
        $kegiatan = KegiatanPanti::factory()->create(['nama_foto' => 'foto.jpg']);
        Storage::put('kegiatan/foto.jpg', 'foto');

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.kegiatan.destroy', $kegiatan))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.kegiatan.destroy', $kegiatan))
            ->assertRedirect(route('admin.kegiatan.index'));

        $this->assertModelMissing($kegiatan);
        Storage::assertMissing('kegiatan/foto.jpg');
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return [
            'nama_kegiatan' => 'Pengajian rutin',
            'jenis_kegiatan' => JenisKegiatan::Ibadah->value,
            'deskripsi' => 'Pengajian bersama anak-anak.',
            'tanggal_kegiatan' => '2026-10-01',
            'lokasi' => 'Aula panti',
            ...$override,
        ];
    }
}
