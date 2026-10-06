<?php

namespace Tests\Feature;

use App\Models\AnakPanti;
use App\Models\BeratBadan;
use App\Models\User;
use Database\Seeders\BeratBadanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeratBadanSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_six_months_for_active_children_once(): void
    {
        $this->travelTo('2026-10-15');
        $aktif = AnakPanti::factory()->count(2)->create();
        $alumni = AnakPanti::factory()->alumni()->create();

        $this->seed(BeratBadanSeeder::class);
        $this->seed(BeratBadanSeeder::class);

        $this->assertSame(12, BeratBadan::count());
        $this->assertSame(0, $alumni->beratBadan()->count());
        $this->assertSame(['2026-05-01', '2026-10-01'], [
            $aktif->first()->beratBadan()->min('bulan'),
            $aktif->first()->beratBadan()->max('bulan'),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Belum ada data berat badan.');
    }
}
