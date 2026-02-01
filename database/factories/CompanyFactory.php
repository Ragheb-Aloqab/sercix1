<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name'   => $this->faker->company(),
            'email'          => $this->faker->unique()->safeEmail(),
            'phone'          => '05' . $this->faker->numberBetween(10000000, 99999999),
            'password'       => Hash::make('password'),
            'phone_verified_at' => now(),
            'contact_person' => $this->faker->name(),
            'city'           => $this->faker->randomElement(['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah']),
            'address'        => $this->faker->streetAddress(),
        ];
    }
}
