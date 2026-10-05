<?php

namespace Database\Factories;

use App\Models\WaliAnak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaliAnak>
 */
class WaliAnakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Nama_Wali' => fake()->name(),
            'Alamat_Wali' => 'Lowokwaru Jl. '.fake()->streetName(),
        ];
    }
}
