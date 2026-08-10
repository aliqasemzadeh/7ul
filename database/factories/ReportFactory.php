<?php

namespace Database\Factories;

use App\Enums\ReportStatusEnum;
use App\Models\Link;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link_id' => Link::factory(),
            'tracking_code' => strtoupper(Str::random(10)),
            'reason' => fake()->sentence(),
            'status' => ReportStatusEnum::Pending,
            'reporter_ip' => fake()->ipv4(),
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReportStatusEnum::Pending,
            'admin_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReportStatusEnum::Accepted,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ReportStatusEnum::Rejected,
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }
}
