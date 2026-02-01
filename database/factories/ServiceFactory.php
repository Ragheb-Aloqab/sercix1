<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/ServiceFactory.php
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'تغيير زيت',
                'تغيير فلتر زيت',
                'تغيير فلتر هواء',
                'كفرات',
                'فحص بطارية'
            ]),
            'base_price' => $this->faker->randomFloat(2, 50, 400),
            'estimated_minutes' => $this->faker->numberBetween(15, 120),
            'is_active' => true,
        ];
    }
}
