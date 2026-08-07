<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\LinkVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkVisit>
 */
class LinkVisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'device_type' => fake()->randomElement(['desktop', 'phone', 'tablet']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'os' => fake()->randomElement(['Windows', 'macOS', 'Android', 'iOS', 'Linux']),
        ];
    }
}
