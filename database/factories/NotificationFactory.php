<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/NotificationFactory.php
    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement(['تحديث طلب', 'فاتورة جاهزة', 'تم إسناد فني']),
            'body' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['order', 'payment', 'system']),
            'is_read' => $this->faker->boolean(30),
        ];
    }
}
