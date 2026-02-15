<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/OrderFactory.php
    public function definition(): array
    {
        
        return [
            'status' => $this->faker->randomElement(['pending_company', 'approved_by_company', 'pending_assignment', 'assigned_to_technician', 'in_progress', 'completed', 'cancelled']),
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+10 days'),
            'city' => $this->faker->randomElement(['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah']),
            'address' => $this->faker->address(),
            'lat' => $this->faker->latitude(24.0, 25.0),
            'lng' => $this->faker->longitude(46.0, 47.0),
            'notes' => $this->faker->sentence(),
       
        ];
    }
}
