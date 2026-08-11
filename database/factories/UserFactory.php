<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mobile' => '09'.fake()->unique()->numerify('#########'),
            'email' => null,
            'password' => null,
            'registration_ip' => fake()->ipv4(),
            'remember_token' => Str::random(10),
        ];
    }

    public function withEmail(?string $email = null): static
    {
        return $this->state(fn (): array => [
            'email' => $email ?? fake()->unique()->safeEmail(),
        ]);
    }

    public function withPassword(?string $password = null): static
    {
        return $this->state(fn (): array => [
            'password' => $password ?? static::$password ??= Hash::make('password'),
        ]);
    }

    public function emailPasswordUser(?string $email = null, ?string $password = null): static
    {
        return $this->state(fn (): array => [
            'mobile' => null,
            'email' => $email ?? fake()->unique()->safeEmail(),
            'password' => $password ?? static::$password ??= Hash::make('password'),
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user): void {
            $role = Role::findOrCreate('admin', 'web');
            $user->assignRole($role);
        });
    }
}
