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
        // return [
        //     'status' => $this->faker->randomElement(['pending', 'assigned', 'on_the_way', 'in_progress', 'completed', 'cancelled']),
        //     'scheduled_at' => $this->faker->dateTimeBetween('-2 days', '+7 days'),
        //     'address' => $this->faker->address(),
        //     'lat' => $this->faker->latitude(24.3, 25.0),
        //     'lng' => $this->faker->longitude(46.3, 47.1),
        //     'notes' => $this->faker->optional()->sentence(),
        //     'payment_method' => $this->faker->randomElement(['cod', 'tap']),
        //     'total' => 0, // نحسبه لاحقاً بعد ربط الخدمات
        // ];
        return [
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+10 days'),
            'city' => $this->faker->randomElement(['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah']),
            'address' => $this->faker->address(),
            'lat' => $this->faker->latitude(24.0, 25.0),
            'lng' => $this->faker->longitude(46.0, 47.0),
            'notes' => $this->faker->sentence(),
            // لا تضع company_id/vehicle_id/technician_id هنا لأننا نمررهم من DemoSeeder
        ];
    }
}
