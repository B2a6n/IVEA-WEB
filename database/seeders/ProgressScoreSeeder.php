<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\User;
use App\Models\UserProgressScore;
use Database\Factories\UserProgressScoreFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Simulates 90 days of synced gameplay. Each student has a skill level so scores look like real progress.
 */
final class ProgressScoreSeeder extends Seeder
{
    private const int MIN_GAMES_PER_STUDENT = 10;

    private const int MAX_GAMES_PER_STUDENT = 30;

    private const int HISTORY_DAYS = 90;

    private const int MAX_SCORE = 100;

    private const int CHUNK_SIZE = 500;

    public function run(): void
    {
        $modules = GameModule::query()->active()->get();
        $configs = ConfigVersion::query()->published()->orderBy('published_at')->get();
        $now = now();

        $rows = User::query()->students()->get()
            ->flatMap(fn (User $student) => $this->gamesFor($student, $modules, $configs, $now));

        $rows->chunk(self::CHUNK_SIZE)->each(
            fn (Collection $chunk) => UserProgressScore::query()->insert($chunk->values()->all())
        );
    }

    /**
     * @param  Collection<int, GameModule>  $modules
     * @param  Collection<int, ConfigVersion>  $configs
     * @return list<array<string, mixed>>
     */
    private function gamesFor(User $student, Collection $modules, Collection $configs, Carbon $now): array
    {
        $skill = fake()->randomFloat(2, 0.3, 0.9);
        $games = fake()->numberBetween(self::MIN_GAMES_PER_STUDENT, self::MAX_GAMES_PER_STUDENT);

        return array_map(function () use ($student, $modules, $configs, $now, $skill): array {
            $module = $modules->random();
            $playedAt = Carbon::instance(fake()->dateTimeBetween('-'.self::HISTORY_DAYS.' days'));
            $difficultyPenalty = ($module->difficulty_level - 2) * 0.1;
            $noise = fake()->randomFloat(2, -0.15, 0.15);
            $successRate = max(0.0, min(1.0, $skill - $difficultyPenalty + $noise));
            $correct = (int) round($successRate * 10);
            $config = $configs->last(fn (ConfigVersion $config) => $config->published_at <= $playedAt);

            return [
                'client_uuid' => fake()->uuid(),
                'user_id' => $student->id,
                'games_module_id' => $module->id,
                'config_version_id' => $config?->id,
                'score' => $correct * 10,
                'max_score' => self::MAX_SCORE,
                'duration_seconds' => fake()->numberBetween(60, 600) + $module->difficulty_level * 60,
                'played_at' => $playedAt,
                'details_json' => json_encode(UserProgressScoreFactory::details($correct), JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, range(1, $games));
    }
}
