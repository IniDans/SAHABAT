<?php

namespace Tests\Feature\Admin;

use App\Enums\KategoriKebutuhan;
use App\Enums\PrioritasKebutuhan;
use App\Models\KebutuhanPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KebutuhanPantiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.kebutuhan-panti.index'))->assertNotFound();
    }

    public function test_index_puts_unfulfilled_high_scores_first_and_filters(): void
    {
        KebutuhanPanti::factory()->terpenuhi()->create(['nama' => 'Buku tulis', 'skor_prioritas' => 99]);
        KebutuhanPanti::factory()->create(['nama' => 'Seragam sekolah', 'skor_prioritas' => 60]);
        KebutuhanPanti::factory()->mendesak()->create(['nama' => 'Beras', 'skor_prioritas' => 95]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kebutuhan-panti.index'))
            ->assertOk()
            ->assertSeeInOrder(['Beras', 'Seragam sekolah', 'Buku tulis']);

        $this->get(route('admin.kebutuhan-panti.index', ['status' => 'terpenuhi']))
            ->assertSee('Buku tulis')
            ->assertDontSee('Seragam sekolah');

        $this->get(route('admin.kebutuhan-panti.index', ['prioritas' => PrioritasKebutuhan::Mendesak->value]))
            ->assertSee('Beras')
            ->assertDontSee('Seragam sekolah');
    }

    public function test_kebutuhan_can_be_created(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.kebutuhan-panti.create'))
            ->assertOk();

        $this->post(route('admin.kebutuhan-panti.store'), $this->payload())
            ->assertRedirect(route('admin.kebutuhan-panti.index'))
            ->assertSessionHas('status');

        $kebutuhan = KebutuhanPanti::sole();
        $this->assertSame('Beras', $kebutuhan->nama);
        $this->assertSame(PrioritasKebutuhan::Mendesak, $kebutuhan->prioritas);
        $this->assertFalse($kebutuhan->terpenuhi);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.kebutuhan-panti.store'), $this->payload(['nama' => '', 'skor_prioritas' => 150]))
            ->assertSessionHasErrors(['nama', 'skor_prioritas']);

        $this->assertDatabaseCount('kebutuhan_panti', 0);
    }

    public function test_kebutuhan_can_be_marked_fulfilled_and_unmarked(): void
    {
        $kebutuhan = KebutuhanPanti::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kebutuhan-panti.edit', $kebutuhan))
            ->assertOk();

        $this->put(route('admin.kebutuhan-panti.update', $kebutuhan), $this->payload(['terpenuhi' => '1']))
            ->assertRedirect(route('admin.kebutuhan-panti.index'));
        $this->assertTrue($kebutuhan->refresh()->terpenuhi);

        // Checkbox yang tidak dicentang tidak ikut terkirim.
        $this->put(route('admin.kebutuhan-panti.update', $kebutuhan), $this->payload());
        $this->assertFalse($kebutuhan->refresh()->terpenuhi);
    }

    public function test_fulfilled_status_can_be_toggled_from_the_list(): void
    {
        $kebutuhan = KebutuhanPanti::factory()->create(['nama' => 'Beras']);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.kebutuhan-panti.index'))
            ->patch(route('admin.kebutuhan-panti.terpenuhi', $kebutuhan))
            ->assertRedirect(route('admin.kebutuhan-panti.index'))
            ->assertSessionHas('status', 'Beras ditandai terpenuhi.');
        $this->assertTrue($kebutuhan->refresh()->terpenuhi);

        $this->patch(route('admin.kebutuhan-panti.terpenuhi', $kebutuhan));
        $this->assertFalse($kebutuhan->refresh()->terpenuhi);
    }

    public function test_index_filters_by_kategori_and_shows_summary(): void
    {
        KebutuhanPanti::factory()->mendesak()->create(['nama' => 'Beras', 'kategori' => KategoriKebutuhan::Pangan]);
        KebutuhanPanti::factory()->terpenuhi()->create(['nama' => 'Laptop', 'kategori' => KategoriKebutuhan::Perlengkapan]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.kebutuhan-panti.index', ['kategori' => KategoriKebutuhan::Pangan->value]))
            ->assertSee('Beras')
            ->assertDontSee('Laptop')
            ->assertViewHas('ringkasan', ['semua' => 2, 'mendesak' => 1, 'belum' => 1, 'terpenuhi' => 1]);
    }

    public function test_only_admin_can_delete_kebutuhan(): void
    {
        $kebutuhan = KebutuhanPanti::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.kebutuhan-panti.destroy', $kebutuhan))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.kebutuhan-panti.destroy', $kebutuhan))
            ->assertRedirect(route('admin.kebutuhan-panti.index'));

        $this->assertModelMissing($kebutuhan);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'nama' => 'Beras',
            'kategori' => KategoriKebutuhan::Pangan->value,
            'jumlah' => 50,
            'satuan' => 'kg',
            'prioritas' => PrioritasKebutuhan::Mendesak->value,
            'skor_prioritas' => 95,
            'keterangan' => 'Untuk stok sebulan.',
            ...$overrides,
        ];
    }
}
