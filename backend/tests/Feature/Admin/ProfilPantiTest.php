<?php

namespace Tests\Feature\Admin;

use App\Models\ProfilPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.profil.edit'))->assertNotFound();
    }

    public function test_each_section_has_an_edit_form(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (array_keys(ProfilPanti::BAGIAN) as $bagian) {
            $this->get(route('admin.profil.edit', ['bagian' => $bagian]))
                ->assertOk()
                ->assertSee(route('admin.profil.update', $bagian), false);
        }
    }

    public function test_unknown_section_is_not_found(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/admin/profil-panti/rahasia', ['apa' => 'saja'])
            ->assertNotFound();
    }

    public function test_contact_details_appear_on_the_public_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.profil.update', 'kontak'), [
                'email' => 'halo@yasibu.org',
                'whatsapp' => [
                    ['nomor' => '0812-3456-7890', 'nama' => 'Bu Rina'],
                    ['nomor' => '', 'nama' => ''],
                ],
                'lokasi_nama' => 'Panti Asuhan YASIBU',
                'lokasi_alamat' => 'Jl. Contoh No. 1, Malang',
                'peta' => 'Jl. Contoh No. 1, Malang',
                'alamat_footer' => 'Jl. Footer No. 9, Malang',
            ])
            ->assertRedirect(route('admin.profil.edit', ['bagian' => 'kontak']))
            ->assertSessionHas('status');

        $this->assertSame([['nomor' => '+6281234567890', 'nama' => 'Bu Rina']], ProfilPanti::bagian('kontak')['whatsapp']);

        $this->get(route('tentang.kontak'))
            ->assertOk()
            ->assertSee('halo@yasibu.org')
            ->assertSee('Bu Rina')
            ->assertSee('wa.me/6281234567890')
            ->assertSee('Jl. Footer No. 9, Malang');
    }

    public function test_mission_is_saved_one_item_per_line(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.profil.update', 'visi_misi'), [
                'motto' => 'Bersama membangun harapan',
                'visi' => 'Menjadi panti yang amanah.',
                'misi' => "Mendidik anak\n\n  Menyantuni dhuafa  \r\nMembina akhlak",
            ])
            ->assertRedirect();

        $this->assertSame(['Mendidik anak', 'Menyantuni dhuafa', 'Membina akhlak'], ProfilPanti::bagian('visi_misi')['misi']);

        $this->get(route('tentang.visi-misi'))
            ->assertSeeInOrder(['Mendidik anak', 'Menyantuni dhuafa', 'Membina akhlak']);
    }

    public function test_text_is_escaped_on_the_public_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.profil.update', 'lembaga'), [
                'judul_legalitas' => 'Legalitas',
                'legalitas' => [['label' => 'Akta', 'nilai' => 'No. 1']],
                'sejarah' => "Berdiri tahun 1990. <script>alert('xss')</script>",
            ])
            ->assertRedirect();

        $this->get(route('tentang.profil'))
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_social_links_must_be_https(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.profil.update', 'sosial'), [
                'facebook' => 'javascript:alert(1)',
                'instagram' => 'https://instagram.com/yasibu',
            ])
            ->assertSessionHasErrors(['facebook' => 'Kolom facebook harus berupa tautan yang diawali https://.']);

        $this->assertNull(ProfilPanti::bagian('sosial')['facebook']);
    }

    public function test_saved_social_links_show_in_the_footer(): void
    {
        $this->actingAs(User::factory()->create())
            ->put(route('admin.profil.update', 'sosial'), ['instagram' => 'https://instagram.com/yasibu'])
            ->assertRedirect();

        $this->get(route('beranda'))->assertSee('https://instagram.com/yasibu');
    }
}
