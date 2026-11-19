<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GameModule>
 */
class GameModuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(3, true));

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'description' => fake()->sentence(12),
            'subject_area' => fake()->randomElement(['lectoescritura', 'matematicas', 'vida_cotidiana']),
            'difficulty_level' => fake()->numberBetween(1, 3),
            'sort_order' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
