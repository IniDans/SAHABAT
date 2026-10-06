<?php

namespace Tests\Feature\Admin;

use App\Enums\KategoriProgram;
use App\Enums\StatusBerita;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.program.index'))->assertNotFound();
    }

    public function test_index_lists_and_filters_program(): void
    {
        Program::factory()->diBeranda()->create(['judul' => 'Beasiswa anak yatim', 'kategori' => KategoriProgram::Beasiswa->value]);
        Program::factory()->draft()->create(['judul' => 'Pelatihan menjahit', 'kategori' => KategoriProgram::Keterampilan->value]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.program.index'))
            ->assertOk()
            ->assertViewHas('jumlahDiBeranda', 1)
            ->assertSee('Beasiswa anak yatim')
            ->assertSee('Pelatihan menjahit');

        $this->get(route('admin.program.index', ['kategori' => KategoriProgram::Keterampilan->value]))
            ->assertSee('Pelatihan menjahit')
            ->assertDontSee('Beasiswa anak yatim');
    }

    public function test_publishing_sets_the_publish_date_once(): void
    {
        $this->travelTo('2026-10-05 10:00');

        $this->actingAs(User::factory()->create())
            ->get(route('admin.program.create'))
            ->assertOk()
            ->assertSee('Tampil di Beranda');

        $this->post(route('admin.program.store'), $this->payload(['aksi' => 'draf', 'gambar' => UploadedFile::fake()->image('beasiswa.jpg')]))
            ->assertRedirect(route('admin.program.edit', Program::sole()))
            ->assertSessionHas('status', 'Program disimpan sebagai draf.');

        $program = Program::sole();
        $this->assertSame('beasiswa-pendidikan', $program->slug);
        $this->assertNull($program->tanggal_terbit);
        $this->assertTrue($program->tampil_di_beranda);
        Storage::disk('public')->assertExists($program->gambar);

        $this->put(route('admin.program.update', $program), $this->payload(['aksi' => 'terbitkan']))
            ->assertSessionHas('status', 'Program diterbitkan.');
        $this->assertSame('2026-10-05', $program->refresh()->tanggal_terbit->toDateString());

        $this->travelTo('2026-10-20 10:00');
        $this->put(route('admin.program.update', $program), $this->payload(['aksi' => 'terbitkan', 'tampil_di_beranda' => '0']));

        $program->refresh();
        $this->assertSame('2026-10-05', $program->tanggal_terbit->toDateString());
        $this->assertFalse($program->tampil_di_beranda);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.program.store'), $this->payload(['judul' => '', 'isi' => '<p> </p>', 'tampil_di_beranda' => 'ya']))
            ->assertSessionHasErrors(['judul', 'isi', 'tampil_di_beranda']);

        $this->assertDatabaseCount('program', 0);
    }

    public function test_beranda_can_be_toggled_from_the_list(): void
    {
        $program = Program::factory()->create(['judul' => 'Santunan bulanan']);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.program.index'))
            ->patch(route('admin.program.beranda', $program))
            ->assertRedirect(route('admin.program.index'))
            ->assertSessionHas('status', 'Santunan bulanan ditampilkan di Beranda.');
        $this->assertTrue($program->refresh()->tampil_di_beranda);

        $this->patch(route('admin.program.beranda', $program));
        $this->assertFalse($program->refresh()->tampil_di_beranda);
    }

    public function test_editor_can_upload_images(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->postJson(route('admin.program.gambar'), ['gambar' => UploadedFile::fake()->image('isi.jpg')])
            ->assertOk();

        $path = str($response->json('url'))->after('/storage/')->value();
        $this->assertStringStartsWith(Program::GAMBAR_FOLDER.'/isi/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_only_admin_can_delete_program(): void
    {
        $gambar = UploadedFile::fake()->image('lama.jpg')->store(Program::GAMBAR_FOLDER, 'public');
        $program = Program::factory()->create(['gambar' => $gambar]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.program.destroy', $program))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.program.destroy', $program))
            ->assertRedirect(route('admin.program.index'));

        $this->assertModelMissing($program);
        Storage::disk('public')->assertMissing($gambar);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'judul' => 'Beasiswa Pendidikan',
            'kategori' => KategoriProgram::Beasiswa->value,
            'ringkasan' => 'Bantuan biaya sekolah bagi anak yatim dan dhuafa.',
            'isi' => '<p>Program ini membantu anak yatim tetap bersekolah.</p>',
            'status' => StatusBerita::Draft->value,
            'tampil_di_beranda' => '1',
            ...$overrides,
        ];
    }
}
