<?php

namespace Tests\Feature;

use App\Enums\ProgramDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Berita;
use App\Models\Donasi;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TulisanPublikTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_shows_only_published_programs_chosen_for_beranda(): void
    {
        Program::factory()->diBeranda()->create(['judul' => 'Beasiswa anak yatim']);
        Program::factory()->create(['judul' => 'Pelatihan menjahit']);
        Program::factory()->draft()->diBeranda()->create(['judul' => 'Program rahasia']);
        Berita::factory()->create(['judul' => 'Buka puasa bersama']);
        Berita::factory()->draft()->create(['judul' => 'Draf kegiatan']);

        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Beasiswa anak yatim')
            ->assertDontSee('Pelatihan menjahit')
            ->assertDontSee('Program rahasia')
            ->assertSee('Buka puasa bersama')
            ->assertDontSee('Draf kegiatan');
    }

    public function test_beranda_hides_the_program_section_when_empty(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertDontSee('program-heading')
            ->assertSee('Belum ada artikel kegiatan.');
    }

    public function test_program_list_filters_by_category_and_search(): void
    {
        Program::factory()->create(['judul' => 'Beasiswa SD', 'kategori' => 'Beasiswa']);
        Program::factory()->create(['judul' => 'Cek kesehatan rutin', 'kategori' => 'Kesehatan']);
        Program::factory()->draft()->create(['judul' => 'Beasiswa draf', 'kategori' => 'Beasiswa']);

        $this->get(route('program.index'))
            ->assertOk()
            ->assertSee(['Beasiswa SD', 'Cek kesehatan rutin'])
            ->assertDontSee('Beasiswa draf');

        $this->get(route('program.index', ['kategori' => 'beasiswa']))
            ->assertViewHas('kategori', 'Beasiswa')
            ->assertSee('Beasiswa SD')
            ->assertDontSee('Cek kesehatan rutin');

        $this->get(route('program.index', ['q' => 'kesehatan']))
            ->assertSee('Cek kesehatan rutin')
            ->assertDontSee('Beasiswa SD');
    }

    public function test_program_detail_shows_isi_and_received_donations_only(): void
    {
        $program = Program::factory()->create([
            'judul' => 'Beasiswa Pendidikan',
            'kategori' => 'Beasiswa',
            'isi' => '<h2>Sasaran Program</h2><p>Anak yatim usia sekolah.</p>',
        ]);
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Budi Santoso', 'tampil_sebagai' => TampilanDonatur::HambaAllah, 'nominal' => 150000, 'program' => ProgramDonasi::Beasiswa]);
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Siti Aminah', 'program' => ProgramDonasi::Zakat]);
        Donasi::factory()->create(['nama_donatur' => 'Belum Transfer']);

        $this->get(route('program.show', $program->slug))
            ->assertOk()
            ->assertSee('<h2>Sasaran Program</h2>', false)
            ->assertViewHas('programDonasi', ProgramDonasi::Beasiswa)
            ->assertSee(['Hamba Allah', 'Rp150.000', 'Siti Aminah'])
            ->assertDontSee('Budi Santoso')
            ->assertDontSee('Belum Transfer');
    }

    public function test_draft_or_unknown_posts_are_not_found(): void
    {
        $draf = Program::factory()->draft()->create();
        $artikelDraf = Berita::factory()->draft()->create();

        $this->get(route('program.show', $draf->slug))->assertNotFound();
        $this->get(route('artikel.show', $artikelDraf->slug))->assertNotFound();
        $this->get(route('artikel.show', 'tidak-ada'))->assertNotFound();
    }

    public function test_artikel_pages_show_published_articles(): void
    {
        $artikel = Berita::factory()->create(['judul' => 'Maulid Nabi di Yasibu', 'kategori' => 'Agama', 'isi' => '<p>Menebar cinta Rasulullah.</p>']);
        $lain = Berita::factory()->create(['judul' => 'Lebaran yatim', 'kategori' => 'Kegiatan']);

        $this->get(route('artikel.index', ['kategori' => 'Agama']))
            ->assertOk()
            ->assertSee('Maulid Nabi di Yasibu')
            ->assertDontSee('Lebaran yatim');

        $this->get(route('artikel.show', $artikel->slug))
            ->assertOk()
            ->assertSee('Menebar cinta Rasulullah.')
            ->assertSee(route('artikel.show', $lain->slug));
    }
}
