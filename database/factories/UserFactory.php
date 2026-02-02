<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * حالات (States)
     */
    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
        ]);
    }

    public function technician(): static
    {
        return $this->state(fn () => [
            'role' => 'technician',
        ]);
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // 🔐 حل جذري: تأكيد وجود faker
        $faker = $this->faker ?? FakerFactory::create();

        return [
            'name'              => $faker->name(),
            'email'             => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'remember_token'    => Str::random(10),
        ];
    }

    /**
     * Unverified email
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
