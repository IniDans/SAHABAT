<?php

namespace Tests\Feature\Admin;

use App\Models\FotoGaleri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.galeri.index'))->assertNotFound();
    }

    public function test_uploaded_photos_appear_first_in_selection_order(): void
    {
        $lama = FotoGaleri::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.galeri.store'), [
                'foto' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
                'keterangan' => 'Lebaran Yatim',
            ])
            ->assertRedirect(route('admin.galeri.index'))
            ->assertSessionHas('status', '2 foto berhasil diunggah.');

        $susunan = FotoGaleri::query()->urut()->get();
        $this->assertCount(3, $susunan);
        $this->assertTrue($susunan->last()->is($lama));
        $this->assertSame(['Lebaran Yatim', 'Lebaran Yatim'], $susunan->take(2)->pluck('keterangan')->all());
        $this->assertStringEndsWith('.jpg', $susunan->first()->gambar);
        Storage::disk('public')->assertExists($susunan->take(2)->pluck('gambar')->all());

        $this->get(route('admin.galeri.index'))->assertOk()->assertSee($susunan->first()->url());
    }

    public function test_upload_rejects_large_or_non_image_files(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.galeri.store'), [
                'foto' => [
                    UploadedFile::fake()->image('besar.jpg')->size(FotoGaleri::UKURAN_MAKS_KB + 1),
                    UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
                ],
            ])
            ->assertSessionHasErrors(['foto.0' => 'Berkas 1 melebihi 5 MB.', 'foto.1']);

        $this->post(route('admin.galeri.store'))->assertSessionHasErrors(['foto' => 'Pilih minimal satu foto.']);

        $this->assertDatabaseCount('galeri', 0);
    }

    public function test_caption_can_be_updated(): void
    {
        $foto = FotoGaleri::factory()->create(['keterangan' => null]);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.galeri.update', $foto), ['keterangan' => 'Buka puasa bersama'])
            ->assertSessionHas('status', 'Keterangan foto disimpan.');

        $this->assertSame('Buka puasa bersama', $foto->refresh()->keterangan);
    }

    public function test_reordering_a_page_keeps_other_photos_in_place(): void
    {
        [$a, $b, $c, $d] = collect(range(1, 4))->map(fn (int $urutan) => FotoGaleri::factory()->create(['urutan' => $urutan]))->all();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.galeri.urutan'), ['urutan' => [$c->id, $b->id]])
            ->assertSessionHas('status', 'Urutan foto disimpan.');

        $this->assertSame([$a->id, $c->id, $b->id, $d->id], FotoGaleri::query()->urut()->pluck('id')->all());

        $this->patch(route('admin.galeri.urutan'), ['urutan' => [$a->id, $a->id]])->assertSessionHasErrors('urutan.0');
    }

    public function test_only_admin_can_delete_photos(): void
    {
        $gambar = UploadedFile::fake()->image('a.jpg')->store(FotoGaleri::GAMBAR_FOLDER, 'public');
        $foto = FotoGaleri::factory()->create(['gambar' => $gambar]);
        $lain = FotoGaleri::factory()->count(2)->create();
        $sisa = FotoGaleri::factory()->create();

        $pengurus = User::factory()->create();
        $this->actingAs($pengurus)->delete(route('admin.galeri.destroy', $foto))->assertForbidden();
        $this->actingAs($pengurus)->delete(route('admin.galeri.hapus-banyak'), ['foto' => $lain->modelKeys()])->assertForbidden();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->delete(route('admin.galeri.destroy', $foto))
            ->assertSessionHas('status', 'Foto berhasil dihapus.');
        $this->assertModelMissing($foto);
        Storage::disk('public')->assertMissing($gambar);

        $this->actingAs($admin)
            ->delete(route('admin.galeri.hapus-banyak'), ['foto' => $lain->modelKeys()])
            ->assertSessionHas('status', '2 foto berhasil dihapus.');
        $this->assertSame([$sisa->id], FotoGaleri::query()->pluck('id')->all());
    }

    public function test_public_gallery_lists_photos_in_order_with_pagination(): void
    {
        FotoGaleri::factory()->create(['urutan' => 2, 'keterangan' => 'Foto kedua']);
        FotoGaleri::factory()->create(['urutan' => 1, 'keterangan' => 'Foto pertama']);
        FotoGaleri::factory()->count(9)->create(['urutan' => 5, 'keterangan' => null]);

        $this->get(route('tentang.galeri'))
            ->assertOk()
            ->assertSeeInOrder(['Foto pertama', 'Foto kedua'])
            ->assertSee('aria-label="Halaman berikutnya"', false);

        $this->get(route('tentang.galeri', ['page' => 2]))
            ->assertOk()
            ->assertDontSee('Foto pertama');
    }

    public function test_public_gallery_shows_empty_state(): void
    {
        $this->get(route('tentang.galeri'))->assertOk()->assertSee('Belum ada foto kegiatan.');
    }
}
