<?php

namespace Tests\Feature;

use App\Models\AnakPanti;
use App\Models\KegiatanPanti;
use App\Models\Pengasuh;
use App\Models\WaliAnak;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_the_panti_sql_dump_once(): void
    {
        if (! is_file(base_path(DemoSeeder::SQL_PATH))) {
            $this->markTestSkipped(DemoSeeder::SQL_PATH.' tidak ada (tidak di-commit).');
        }

        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(63, AnakPanti::count());
        $this->assertSame(54, WaliAnak::count());
        $this->assertSame(4, Pengasuh::count());
        $this->assertSame(1, KegiatanPanti::count());

        $this->assertSame('Mimin Indah Lestari', AnakPanti::find('3506261110080002')->wali->Nama_Wali);
        $this->assertSame(AnakPanti::aktif()->count(), DB::table('v_anak_aktif_lengkap')->count());
    }

    public function test_public_page_lists_only_active_children(): void
    {
        AnakPanti::factory()->create(['Nama' => 'ANAK AKTIF']);
        AnakPanti::factory()->alumni()->create(['Nama' => 'ANAK ALUMNI']);

        $this->get(route('tentang.anak-asuh'))
            ->assertOk()
            ->assertSee('ANAK AKTIF')
            ->assertDontSee('ANAK ALUMNI');
    }
}
