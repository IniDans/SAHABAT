<?php

namespace Tests\Feature\Admin;

use App\Models\Donasi;
use App\Models\KebutuhanPanti;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_stats_from_the_database(): void
    {
        $this->travelTo('2026-10-15');

        KebutuhanPanti::factory()->mendesak()->count(2)->create();
        KebutuhanPanti::factory()->mendesak()->terpenuhi()->create();
        Donasi::factory()->diterima()->create(['nominal' => 12_000_000, 'tanggal_donasi' => '2026-10-02']);
        Donasi::factory()->diterima()->create(['nominal' => 500_000, 'tanggal_donasi' => '2026-10-10']);
        Donasi::factory()->diterima()->create(['nominal' => 9_000_000, 'tanggal_donasi' => '2026-09-30']);
        Donasi::factory()->create(['nominal' => 7_000_000, 'tanggal_donasi' => '2026-10-05']);

        $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

        $response->assertOk()->assertSee('Rp 12,5 jt');
        $stats = collect($response->viewData('stats'))->keyBy('label');
        $this->assertSame('2', $stats['Kebutuhan mendesak']['value']);
        $this->assertSame('Rp 12,5 jt', $stats['Donasi bulan ini']['value']);
    }

    public function test_dashboard_lists_unfulfilled_needs_first(): void
    {
        KebutuhanPanti::factory()->terpenuhi()->create(['nama' => 'Buku tulis', 'skor_prioritas' => 99]);
        KebutuhanPanti::factory()->mendesak()->create(['nama' => 'Beras', 'skor_prioritas' => 90]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertSeeInOrder(['Beras', 'Buku tulis']);
    }

    public function test_dashboard_shows_empty_state_without_needs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Belum ada kebutuhan.');
    }
}
