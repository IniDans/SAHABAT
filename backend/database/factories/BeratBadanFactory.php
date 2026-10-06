<?php

namespace Database\Factories;

use App\Models\AnakPanti;
use App\Models\BeratBadan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BeratBadan>
 */
class BeratBadanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => AnakPanti::factory(),
            'bulan' => now()->startOfMonth()->toDateString(),
            'berat_kg' => fake()->randomFloat(1, 15, 50),
        ];
    }
}
