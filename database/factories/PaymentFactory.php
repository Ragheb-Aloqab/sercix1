<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/PaymentFactory.php
    public function definition(): array
    {
       return [
            // order_id + company_id نمررهم من Seeder
            'method' => $this->faker->randomElement(['cash', 'tap']),
            'status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'amount' => $this->faker->randomFloat(2, 20, 800),

            'tap_charge_id' => null,
            'tap_reference' => $this->faker->uuid(),
            'tap_payload' => null,

            'paid_at' => now(),
        ];
    }
}
