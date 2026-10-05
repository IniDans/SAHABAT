<?php

namespace Tests\Feature\Admin;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
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

    public function test_index_defaults_to_this_month_and_totals_received_per_program(): void
    {
        $this->travelTo('2026-10-05 10:00');
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Ibu Ratna', 'program' => ProgramDonasi::Zakat, 'nominal' => 150000, 'tanggal_donasi' => '2026-10-02']);
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Bapak Andi', 'program' => ProgramDonasi::Zakat, 'nominal' => 50000, 'tanggal_donasi' => '2026-09-15']);
        Donasi::factory()->create(['nama_donatur' => 'Siti Rahma', 'program' => ProgramDonasi::Wakaf, 'nominal' => 1000000, 'tanggal_donasi' => '2026-10-03']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.index'))
            ->assertOk()
            ->assertViewHas('totalDiterima', 150000)
            ->assertViewHas('totalPerProgram', fn ($totals) => $totals[ProgramDonasi::Zakat->value] === 150000 && $totals[ProgramDonasi::Wakaf->value] === 0)
            ->assertViewHas('jumlahMenunggu', 1)
            ->assertSee(['Donasi bulan ini', 'Ibu Ratna', 'Siti Rahma'])
            ->assertDontSee('Bapak Andi');

        $this->get(route('admin.donasi.index', ['bulan' => 'semua']))
            ->assertViewHas('totalDiterima', 200000)
            ->assertSee('Bapak Andi');

        $this->get(route('admin.donasi.index', ['bulan' => '2026-09']))
            ->assertViewHas('totalDiterima', 50000)
            ->assertDontSee('Ibu Ratna');
    }

    public function test_index_filters_and_sorts(): void
    {
        $this->travelTo('2026-10-05 10:00');
        Donasi::factory()->diterima()->create(['nama_donatur' => 'Ibu Ratna', 'email' => 'ratna@example.com', 'nominal' => 150000, 'tanggal_donasi' => '2026-10-02']);
        Donasi::factory()->create(['nama_donatur' => 'Siti Rahma', 'email' => 'siti@example.com', 'nominal' => 1000000, 'tanggal_donasi' => '2026-10-01']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.index'))
            ->assertSeeInOrder(['Ibu Ratna', 'Siti Rahma']);

        $this->get(route('admin.donasi.index', ['urut' => 'terbesar']))
            ->assertSeeInOrder(['Siti Rahma', 'Ibu Ratna']);

        $this->get(route('admin.donasi.index', ['search' => 'siti@']))
            ->assertSee('Siti Rahma')
            ->assertDontSee('Ibu Ratna');

        $this->get(route('admin.donasi.index', ['status' => StatusDonasi::Menunggu->value]))
            ->assertSee('Siti Rahma')
            ->assertDontSee('Ibu Ratna');
    }

    public function test_filtered_donasi_can_be_exported_as_csv(): void
    {
        $this->travelTo('2026-10-05 10:00');
        Donasi::factory()->create(['nama_donatur' => 'Ibu Ratna', 'program' => ProgramDonasi::Zakat, 'nominal' => 150000, 'tanggal_donasi' => '2026-10-02']);
        Donasi::factory()->create(['nama_donatur' => 'Bapak Andi', 'program' => ProgramDonasi::Wakaf, 'tanggal_donasi' => '2026-10-03']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.ekspor', ['program' => ProgramDonasi::Zakat->value]))
            ->assertOk()
            ->assertDownload('donasi-2026-10.csv');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Nama donatur', $csv);
        $this->assertStringContainsString('2026-10-02,"Ibu Ratna"', $csv);
        $this->assertStringNotContainsString('Bapak Andi', $csv);
    }

    public function test_whatsapp_number_is_formatted_for_display(): void
    {
        $this->assertSame('+62 812-3456-7890', Donasi::factory()->make(['no_whatsapp' => '081234567890'])->whatsappTampil());
        $this->assertSame('+62 857-1122-334', Donasi::factory()->make(['no_whatsapp' => '+62 857 1122 334'])->whatsappTampil());
        $this->assertNull(Donasi::factory()->make(['no_whatsapp' => null])->whatsappTampil());
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
        $this->assertSame(ProgramDonasi::Infak, $donasi->program);
        $this->assertSame(StatusDonasi::Diterima, $donasi->status);
        $this->assertSame(TampilanDonatur::HambaAllah, $donasi->tampil_sebagai);
        $this->assertSame('Jl. Kembang Kertas No.09, Malang', $donasi->alamat);
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
            'tampil_sebagai' => TampilanDonatur::HambaAllah->value,
            'no_whatsapp' => '081234567890',
            'email' => 'ratna@example.com',
            'alamat' => 'Jl. Kembang Kertas No.09, Malang',
            'program' => ProgramDonasi::Infak->value,
            'nominal' => 250000,
            'metode_pembayaran' => MetodePembayaran::Bsi->value,
            'tanggal_donasi' => '2026-10-01',
            'status' => StatusDonasi::Diterima->value,
            'keterangan' => 'Transfer pagi',
            ...$overrides,
        ];
    }
}
