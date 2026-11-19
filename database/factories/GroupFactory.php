<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Círculo de Estudio '.fake()->unique()->numerify('###'),
            'zone' => fake()->randomElement(Group::ZONES),
            'tutor_id' => User::factory()->tutor(),
            'is_active' => true,
        ];
    }

    public function inZone(string $zone): static
    {
        return $this->state(fn (array $attributes) => [
            'zone' => $zone,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
