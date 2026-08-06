<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => null,
            'mobile_verified_at' => now(),
            'registration_ip' => fake()->ipv4(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the mobile number is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
            'email_verified_at' => null,
        ]);
    }
}
