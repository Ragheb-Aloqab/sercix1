<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyBranch>
 */
class CompanyBranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/CompanyBranchFactory.php
    public function definition(): array
    {
        return [
            'name' => 'فرع ' . $this->faker->city(),
            'contact_person' => $this->faker->name(),
            'phone' => '05' . $this->faker->numberBetween(10000000, 99999999),
            'email' => $this->faker->unique()->safeEmail(),

            'city' => $this->faker->randomElement(['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah']),
            'district' => $this->faker->streetName(),
            'address_line' => $this->faker->streetAddress(),

            'lat' => $this->faker->latitude(24.0, 25.0),
            'lng' => $this->faker->longitude(46.0, 47.0),

            'is_default' => false,
            'is_active' => true,
            // company_id نمرره من Seeder
        ];
    }
}
