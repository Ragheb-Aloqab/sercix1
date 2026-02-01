<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    // database/factories/AttachmentFactory.php
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['before', 'after', 'invoice', 'signature']),
            'path' => 'attachments/demo/' . $this->faker->uuid() . '.jpg',
        ];
    }
}
