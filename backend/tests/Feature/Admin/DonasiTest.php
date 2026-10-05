<?php

namespace Tests\Feature\Admin;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Models\Donasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.donasi.index'))->assertNotFound();
    }

    public function test_index_shows_total_received_for_the_filter(): void
    {
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Ibu Ratna', 'nominal' => 150000, 'tanggal_donasi' => '2026-10-02']);
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Bapak Andi', 'nominal' => 50000, 'tanggal_donasi' => '2026-09-15']);
        Donasi::factory()->create(['nama_donatur' => 'Hamba Allah', 'nominal' => 1000000, 'tanggal_donasi' => '2026-10-03']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.index'))
            ->assertOk()
            ->assertViewHas('totalDiterima', 200000)
            ->assertViewHas('jumlahMenunggu', 1)
            ->assertSee('Rp200.000');

        $this->get(route('admin.donasi.index', ['dari' => '2026-10-01']))
            ->assertViewHas('totalDiterima', 150000)
            ->assertSee('Ibu Ratna')
            ->assertDontSee('Bapak Andi');

        $this->get(route('admin.donasi.index', ['status' => StatusDonasi::Menunggu->value]))
            ->assertSee('Hamba Allah')
            ->assertDontSee('Ibu Ratna');
    }

    public function test_donasi_can_be_recorded(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.create'))
            ->assertOk();

        $this->post(route('admin.donasi.store'), $this->payload())
            ->assertRedirect(route('admin.donasi.index'))
            ->assertSessionHas('status');

        $donasi = Donasi::sole();
        $this->assertSame('Ibu Ratna', $donasi->nama_donatur);
        $this->assertSame(250000, $donasi->nominal);
        $this->assertSame(ProgramDonasi::InfaqSedekah, $donasi->program);
        $this->assertSame(StatusDonasi::Diterima, $donasi->status);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.donasi.store'), $this->payload([
                'nominal' => 500,
                'metode_pembayaran' => 'Dana',
                'tanggal_donasi' => today()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['nominal', 'metode_pembayaran', 'tanggal_donasi']);

        $this->assertDatabaseCount('donasi', 0);
    }

    public function test_donasi_can_be_updated(): void
    {
        $donasi = Donasi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.edit', $donasi))
            ->assertOk();

        $this->put(route('admin.donasi.update', $donasi), $this->payload(['nominal' => 300000]))
            ->assertRedirect(route('admin.donasi.index'));

        $this->assertSame(300000, $donasi->refresh()->nominal);
    }

    public function test_status_can_be_changed_from_the_list(): void
    {
        $donasi = Donasi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->from(route('admin.donasi.index'))
            ->patch(route('admin.donasi.status', $donasi), ['status' => StatusDonasi::Ditolak->value])
            ->assertRedirect(route('admin.donasi.index'))
            ->assertSessionHas('status');

        $this->assertSame(StatusDonasi::Ditolak, $donasi->refresh()->status);

        $this->patch(route('admin.donasi.status', $donasi), ['status' => 'Batal'])
            ->assertSessionHasErrors('status');
    }

    public function test_only_admin_can_delete_donasi(): void
    {
        $donasi = Donasi::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.donasi.destroy', $donasi))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.donasi.destroy', $donasi))
            ->assertRedirect(route('admin.donasi.index'));

        $this->assertModelMissing($donasi);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'nama_donatur' => 'Ibu Ratna',
            'no_whatsapp' => '081234567890',
            'email' => 'ratna@example.com',
            'program' => ProgramDonasi::InfaqSedekah->value,
            'nominal' => 250000,
            'metode_pembayaran' => MetodePembayaran::Bsi->value,
            'tanggal_donasi' => '2026-10-01',
            'status' => StatusDonasi::Diterima->value,
            'keterangan' => 'Transfer pagi',
            ...$overrides,
        ];
    }
}
