<?php

namespace Database\Factories;

use App\Enums\LinkTypeEnum;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'destination' => fake()->url(),
            'short_code' => Str::random(8),
            'type' => LinkTypeEnum::Link,
            'creator_ip' => fake()->ipv4(),
            'is_public_stats' => true,
        ];
    }

    public function privateStats(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_public_stats' => false,
        ]);
    }

    public function ofType(LinkTypeEnum $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }
}
