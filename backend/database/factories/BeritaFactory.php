<?php

namespace Database\Factories;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use App\Models\Berita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Berita>
 */
class BeritaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(6),
            'kategori' => fake()->randomElement(KategoriBerita::cases())->value,
            'ringkasan' => fake()->sentence(15),
            'isi' => fake()->paragraphs(3, true),
            'status' => StatusBerita::Terbit,
            'tanggal_terbit' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
        ];
    }

    /**
     * Berita yang belum diterbitkan.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusBerita::Draft,
        ]);
    }
}
