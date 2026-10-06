<?php

namespace Database\Factories;

use App\Models\FotoGaleri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FotoGaleri>
 */
class FotoGaleriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gambar' => FotoGaleri::GAMBAR_FOLDER.'/'.fake()->uuid().'.jpg',
            'keterangan' => fake()->optional()->sentence(4),
            'urutan' => 0,
        ];
    }
}
