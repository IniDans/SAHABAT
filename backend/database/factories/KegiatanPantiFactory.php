<?php

namespace Database\Factories;

use App\Enums\JenisKegiatan;
use App\Models\KegiatanPanti;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KegiatanPanti>
 */
class KegiatanPantiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kegiatan' => fake()->sentence(4),
            'jenis_kegiatan' => fake()->randomElement(JenisKegiatan::cases()),
            'deskripsi' => fake()->paragraph(),
            'tanggal_kegiatan' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'lokasi' => 'Panti Yasibu',
        ];
    }
}
