<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechnicianLocation>
 */
class TechnicianLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/TechnicianLocationFactory.php
    public function definition(): array
    {
        return [
            'lat' => $this->faker->latitude(24.3, 25.0),
            'lng' => $this->faker->longitude(46.3, 47.1),
            'recorded_at' => now(),
        ];
    }
}
