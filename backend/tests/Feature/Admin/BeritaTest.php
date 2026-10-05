<?php

namespace Tests\Feature\Admin;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BeritaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.berita.index'))->assertNotFound();
    }

    public function test_index_lists_and_filters_berita(): void
    {
        Berita::factory()->create(['judul' => 'Santunan anak yatim', 'kategori' => KategoriBerita::Kegiatan->value]);
        Berita::factory()->draft()->create(['judul' => 'Pengajian rutin', 'kategori' => KategoriBerita::Agama->value]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.berita.index'))
            ->assertOk()
            ->assertSee('Santunan anak yatim')
            ->assertSee('Pengajian rutin');

        $this->get(route('admin.berita.index', ['search' => 'santunan']))
            ->assertSee('Santunan anak yatim')
            ->assertDontSee('Pengajian rutin');

        $this->get(route('admin.berita.index', ['status' => StatusBerita::Draft->value]))
            ->assertSee('Pengajian rutin')
            ->assertDontSee('Santunan anak yatim');
    }

    public function test_berita_can_be_created_with_image(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.berita.create'))
            ->assertOk();

        $this->post(route('admin.berita.store'), $this->payload(['gambar' => UploadedFile::fake()->image('kegiatan.jpg')]))
            ->assertRedirect(route('admin.berita.edit', Berita::sole()))
            ->assertSessionHas('status', 'Artikel diterbitkan.');

        $berita = Berita::sole();
        $this->assertSame('Buka puasa bersama', $berita->judul);
        $this->assertSame('buka-puasa-bersama', $berita->slug);
        $this->assertSame(StatusBerita::Terbit, $berita->status);
        Storage::disk('public')->assertExists($berita->gambar);
    }

    public function test_slug_stays_unique(): void
    {
        Berita::factory()->create(['judul' => 'Buka puasa bersama']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.berita.store'), $this->payload());

        $this->assertSame('buka-puasa-bersama-2', Berita::latest('id')->first()->slug);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.berita.store'), $this->payload(['judul' => '', 'kategori' => '', 'ringkasan' => str_repeat('a', 161), 'isi' => '<p> </p>']))
            ->assertSessionHasErrors(['judul', 'kategori', 'ringkasan', 'isi']);

        $this->assertDatabaseCount('berita', 0);
    }

    public function test_updating_replaces_the_old_image(): void
    {
        $lama = UploadedFile::fake()->image('lama.jpg')->store(Berita::GAMBAR_FOLDER, 'public');
        $berita = Berita::factory()->create(['gambar' => $lama]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.berita.edit', $berita))
            ->assertOk()
            ->assertSee($berita->judul);

        $this->put(route('admin.berita.update', $berita), $this->payload(['judul' => 'Judul baru', 'gambar' => UploadedFile::fake()->image('baru.jpg')]))
            ->assertRedirect(route('admin.berita.edit', $berita));

        $berita->refresh();
        $this->assertSame('judul-baru', $berita->slug);
        Storage::disk('public')->assertMissing($lama);
        Storage::disk('public')->assertExists($berita->gambar);
    }

    public function test_image_can_be_removed_without_replacement(): void
    {
        $lama = UploadedFile::fake()->image('lama.jpg')->store(Berita::GAMBAR_FOLDER, 'public');
        $berita = Berita::factory()->create(['gambar' => $lama]);

        $this->actingAs(User::factory()->create())
            ->put(route('admin.berita.update', $berita), $this->payload(['hapus_gambar' => '1']));

        $this->assertNull($berita->refresh()->gambar);
        Storage::disk('public')->assertMissing($lama);
    }

    public function test_aksi_button_decides_the_status(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.berita.store'), $this->payload(['aksi' => 'draf']))
            ->assertSessionHas('status', 'Artikel disimpan sebagai draf.');

        $berita = Berita::sole();
        $this->assertSame(StatusBerita::Draft, $berita->status);

        $this->put(route('admin.berita.update', $berita), $this->payload(['status' => StatusBerita::Draft->value, 'aksi' => 'terbitkan']));

        $this->assertSame(StatusBerita::Terbit, $berita->refresh()->status);
    }

    public function test_isi_is_sanitized(): void
    {
        $isi = '<h2 style="color:red">Kegiatan</h2><p onclick="alert(1)">Halo <strong>semua</strong></p>'
            .'<script>alert(1)</script><p data-baca-juga=""><a href="/artikel/lain">Baca juga: Lain</a></p>'
            .'<p><a href="javascript:alert(1)">jahat</a></p>';

        $this->actingAs(User::factory()->create())
            ->post(route('admin.berita.store'), $this->payload(['isi' => $isi]));

        $tersimpan = Berita::sole()->isi;
        $this->assertStringContainsString('<h2>Kegiatan</h2>', $tersimpan);
        $this->assertStringContainsString('<strong>semua</strong>', $tersimpan);
        $this->assertStringContainsString('data-baca-juga', $tersimpan);
        $this->assertStringContainsString('href="/artikel/lain"', $tersimpan);
        $this->assertStringNotContainsString('script', $tersimpan);
        $this->assertStringNotContainsString('onclick', $tersimpan);
        $this->assertStringNotContainsString('style', $tersimpan);
        $this->assertStringNotContainsString('javascript:', $tersimpan);
    }

    public function test_new_kategori_is_saved_and_case_is_unified(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.berita.store'), $this->payload(['kategori' => '  ramadhan  ceria ']));

        $this->assertSame('Ramadhan ceria', Berita::sole()->kategori);
        $this->assertContains('Ramadhan ceria', Berita::daftarKategori());

        $this->post(route('admin.berita.store'), $this->payload(['judul' => 'Lain', 'kategori' => 'RAMADHAN CERIA']));

        $this->assertSame('Ramadhan ceria', Berita::latest('id')->first()->kategori);
    }

    public function test_editor_can_upload_images(): void
    {
        $this->post(route('admin.berita.gambar'))->assertNotFound();

        $response = $this->actingAs(User::factory()->create())
            ->postJson(route('admin.berita.gambar'), ['gambar' => UploadedFile::fake()->image('isi.jpg')])
            ->assertOk();

        $path = str($response->json('url'))->after('/storage/')->value();
        $this->assertStringStartsWith(Berita::GAMBAR_FOLDER.'/isi/', $path);
        Storage::disk('public')->assertExists($path);

        $this->postJson(route('admin.berita.gambar'), ['gambar' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf')])
            ->assertJsonValidationErrors('gambar');
    }

    public function test_only_admin_can_delete_berita(): void
    {
        $lama = UploadedFile::fake()->image('lama.jpg')->store(Berita::GAMBAR_FOLDER, 'public');
        $berita = Berita::factory()->create(['gambar' => $lama]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.berita.destroy', $berita))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.berita.destroy', $berita))
            ->assertRedirect(route('admin.berita.index'));

        $this->assertModelMissing($berita);
        Storage::disk('public')->assertMissing($lama);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'judul' => 'Buka puasa bersama',
            'kategori' => KategoriBerita::Acara->value,
            'ringkasan' => 'Buka puasa bersama donatur.',
            'isi' => 'Alhamdulillah acara berjalan lancar.',
            'status' => StatusBerita::Terbit->value,
            'tanggal_terbit' => '2026-10-01',
            ...$overrides,
        ];
    }
}
