<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\User;
use App\Models\UserProgressScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProgressScore>
 */
class UserProgressScoreFactory extends Factory
{
    private const int QUESTIONS_PER_GAME = 10;

    private const array LEVELS = ['inicial', 'intermedio', 'avanzado'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $correct = fake()->numberBetween(0, self::QUESTIONS_PER_GAME);

        return [
            'client_uuid' => fake()->uuid(),
            'user_id' => User::factory()->student(),
            'games_module_id' => GameModule::factory(),
            'config_version_id' => ConfigVersion::factory(),
            'score' => $correct * 10,
            'max_score' => self::QUESTIONS_PER_GAME * 10,
            'duration_seconds' => fake()->numberBetween(45, 900),
            'played_at' => fake()->dateTimeBetween('-90 days'),
            'details_json' => self::details($correct),
        ];
    }

    /**
     * Gameplay breakdown as reported by the mobile app.
     *
     * @return array<string, mixed>
     */
    public static function details(int $correct): array
    {
        $wrong = self::QUESTIONS_PER_GAME - $correct;

        return [
            'correct' => $correct,
            'wrong' => $wrong,
            'hints_used' => fake()->numberBetween(0, min(3, $wrong)),
            'attempts' => array_map(
                fn () => fake()->numberBetween(1, 3),
                range(1, self::QUESTIONS_PER_GAME),
            ),
            'level_reached' => self::LEVELS[min(2, intdiv($correct, 4))],
            'device' => fake()->randomElement(['android', 'android', 'android', 'ios']),
        ];
    }
}
