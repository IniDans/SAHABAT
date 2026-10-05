<?php

namespace Database\Factories;

use App\Enums\PrioritasKebutuhan;
use App\Models\KebutuhanPanti;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KebutuhanPanti>
 */
class KebutuhanPantiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['Beras', 'Susu anak', 'Seragam sekolah', 'Buku tulis', 'Minyak goreng']),
            'jumlah' => fake()->numberBetween(1, 100),
            'satuan' => fake()->randomElement(['kg', 'pcs', 'paket', 'liter']),
            'prioritas' => PrioritasKebutuhan::Sedang,
            'skor_prioritas' => fake()->numberBetween(0, 100),
            'terpenuhi' => false,
        ];
    }

    /**
     * Kebutuhan mendesak yang belum terpenuhi.
     */
    public function mendesak(): static
    {
        return $this->state(fn (array $attributes) => [
            'prioritas' => PrioritasKebutuhan::Mendesak,
            'skor_prioritas' => fake()->numberBetween(80, 100),
        ]);
    }

    /**
     * Kebutuhan yang sudah terpenuhi.
     */
    public function terpenuhi(): static
    {
        return $this->state(fn (array $attributes) => [
            'terpenuhi' => true,
        ]);
    }
}
