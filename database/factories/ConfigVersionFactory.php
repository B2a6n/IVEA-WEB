<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConfigVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfigVersion>
 */
class ConfigVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'version' => fake()->unique()->numberBetween(1, 100000),
            'payload' => [
                'min_app_version' => fake()->randomElement(['1.0.0', '1.1.0', '1.2.0']),
                'sync_interval_minutes' => fake()->randomElement([15, 30, 60]),
                'max_offline_days' => fake()->randomElement([7, 14]),
                'audio_hints_enabled' => fake()->boolean(70),
            ],
            'notes' => fake()->sentence(),
            'published_by' => User::factory()->admin(),
            'published_at' => fake()->dateTimeBetween('-90 days', '-1 day'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_by' => null,
            'published_at' => null,
        ]);
    }
}
