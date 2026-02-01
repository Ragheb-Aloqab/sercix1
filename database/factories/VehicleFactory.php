<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/VehicleFactory.php
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['sedan', 'suv', 'pickup', 'van']),
            'make' => $this->faker->randomElement(['Toyota', 'Hyundai', 'Nissan', 'Kia', 'Ford']),
            'model' => $this->faker->randomElement(['Camry', 'Elantra', 'Sunny', 'Sportage', 'Ranger']),
            'year' => $this->faker->numberBetween(2015, 2025),
            'plate_number' => $this->faker->bothify('?? ####'),
            'color' => $this->faker->safeColorName(),
        ];
    }
}
