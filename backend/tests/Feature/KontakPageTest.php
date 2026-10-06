<?php

namespace Tests\Feature;

use App\Enums\StatusPesan;
use App\Models\Pesan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KontakPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_is_stored_as_unread(): void
    {
        $this->post(route('tentang.kontak.store'), [
            'nama' => 'Ibu Ratna',
            'email' => 'ratna@example.com',
            'subjek' => 'Kunjungan ke panti',
            'isi' => 'Kami ingin berkunjung hari Sabtu.',
        ])
            ->assertRedirect(route('tentang.kontak'))
            ->assertSessionHas('status');

        $pesan = Pesan::sole();
        $this->assertSame('Ibu Ratna', $pesan->nama);
        $this->assertSame('Kami ingin berkunjung hari Sabtu.', $pesan->isi);
        $this->assertSame(StatusPesan::BelumDibaca, $pesan->status);

        $this->get(route('tentang.kontak'))->assertSee('pesan Anda sudah terkirim');
    }

    public function test_invalid_contact_message_is_rejected(): void
    {
        $this->post(route('tentang.kontak.store'), [
            'nama' => '',
            'email' => 'bukan-email',
            'isi' => '',
        ])->assertSessionHasErrors(['nama', 'email', 'isi']);

        $this->assertDatabaseCount('pesan', 0);
    }
}
