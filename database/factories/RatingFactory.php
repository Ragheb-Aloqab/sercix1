<?php

namespace Database\Factories;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        $speed = $this->faker->numberBetween(1, 5);
        $quality = $this->faker->numberBetween(1, 5);
        $behavior = $this->faker->numberBetween(1, 5);

        $overall = (int) round(($speed + $quality + $behavior) / 3);

        return [
            'speed' => $speed,
            'quality' => $quality,
            'behavior' => $behavior,
            'overall' => $overall,
            'comment' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
