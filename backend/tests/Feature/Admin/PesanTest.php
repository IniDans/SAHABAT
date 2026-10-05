<?php

namespace Tests\Feature\Admin;

use App\Enums\StatusPesan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.pesan.index'))->assertNotFound();
    }

    public function test_index_counts_per_status_and_filters(): void
    {
        $this->travelTo('2026-10-05 10:00');
        Pesan::factory()->create(['nama' => 'Ratna Sari', 'subjek' => 'Donasi sembako bulanan', 'created_at' => '2026-10-05 09:12']);
        Pesan::factory()->status(StatusPesan::Dibalas)->create(['nama' => 'Dewi Lestari', 'subjek' => 'Konfirmasi donasi', 'created_at' => '2026-10-04 16:20']);
        Pesan::factory()->status(StatusPesan::Diarsipkan)->create(['nama' => 'Hendra Wijaya', 'subjek' => 'Wakaf Al-Quran', 'created_at' => '2026-09-20 13:27']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.pesan.index'))
            ->assertOk()
            ->assertViewHas('jumlahTotal', 3)
            ->assertViewHas('jumlahPerStatus', fn ($jumlah) => $jumlah[StatusPesan::BelumDibaca->value] === 1 && $jumlah[StatusPesan::Diarsipkan->value] === 1)
            ->assertSeeInOrder(['Ratna Sari', 'Dewi Lestari', 'Hendra Wijaya']);

        $this->get(route('admin.pesan.index', ['bulan' => '2026-10']))
            ->assertViewHas('jumlahTotal', 2)
            ->assertDontSee('Hendra Wijaya');

        $this->get(route('admin.pesan.index', ['status' => StatusPesan::Dibalas->value]))
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Ratna Sari');

        $this->get(route('admin.pesan.index', ['search' => 'sembako']))
            ->assertSee('Ratna Sari')
            ->assertDontSee('Dewi Lestari');

        $this->get(route('admin.pesan.index', ['urut' => 'terlama']))
            ->assertSeeInOrder(['Hendra Wijaya', 'Dewi Lestari', 'Ratna Sari']);
    }

    public function test_sidebar_shows_unread_count(): void
    {
        Pesan::factory()->count(2)->create();
        Pesan::factory()->status(StatusPesan::Dibaca)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Pesan masuk (2 belum dibaca)');
    }

    public function test_opening_a_message_marks_it_read(): void
    {
        $pesan = Pesan::factory()->create(['isi' => 'Kami ingin berkunjung hari Sabtu.']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.pesan.show', $pesan))
            ->assertOk()
            ->assertSee('Kami ingin berkunjung hari Sabtu.');

        $this->assertSame(StatusPesan::Dibaca, $pesan->refresh()->status);
    }

    public function test_replying_marks_it_replied_and_opens_email(): void
    {
        $pesan = Pesan::factory()->create(['nama' => 'Ratna', 'email' => 'ratna@example.com', 'subjek' => 'Kunjungan ke panti', 'isi' => "Baris satu\nBaris dua"]);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('admin.pesan.balas', $pesan))
            ->assertRedirect();

        $this->assertSame(StatusPesan::Dibalas, $pesan->refresh()->status);

        $mailto = $response->headers->get('Location');
        $this->assertStringStartsWith('mailto:ratna%40example.com?', $mailto);
        parse_str(parse_url($mailto, PHP_URL_QUERY), $query);
        $this->assertSame('Re: Kunjungan ke panti', $query['subject']);
        $this->assertStringContainsString("> Baris satu\n> Baris dua", $query['body']);
    }

    public function test_status_can_be_changed(): void
    {
        $pesan = Pesan::factory()->status(StatusPesan::Dibaca)->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.pesan.show', $pesan))
            ->patch(route('admin.pesan.status', $pesan), ['status' => StatusPesan::Diarsipkan->value])
            ->assertRedirect(route('admin.pesan.show', $pesan));
        $this->assertSame(StatusPesan::Diarsipkan, $pesan->refresh()->status);

        // Kembali ke detail akan menandainya dibaca lagi, jadi diarahkan ke daftar.
        $this->from(route('admin.pesan.show', $pesan))
            ->patch(route('admin.pesan.status', $pesan), ['status' => StatusPesan::BelumDibaca->value])
            ->assertRedirect(route('admin.pesan.index'));
        $this->assertSame(StatusPesan::BelumDibaca, $pesan->refresh()->status);

        $this->patch(route('admin.pesan.status', $pesan), ['status' => 'Dihapus'])
            ->assertSessionHasErrors('status');
    }

    public function test_all_unread_messages_can_be_marked_read(): void
    {
        Pesan::factory()->count(3)->create();
        $dibalas = Pesan::factory()->status(StatusPesan::Dibalas)->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.pesan.tandai-dibaca'))
            ->assertSessionHas('status', '3 pesan ditandai sudah dibaca.');

        $this->assertSame(0, Pesan::where('status', StatusPesan::BelumDibaca)->count());
        $this->assertSame(StatusPesan::Dibalas, $dibalas->refresh()->status);
    }

    public function test_only_admin_can_delete_messages(): void
    {
        $pesan = Pesan::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.pesan.destroy', $pesan))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.pesan.destroy', $pesan))
            ->assertRedirect(route('admin.pesan.index'));

        $this->assertModelMissing($pesan);
    }
}
