<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserProgressScoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_uuid', 'user_id', 'games_module_id', 'config_version_id', 'score', 'max_score',
    'duration_seconds', 'played_at', 'details_json',
])]
class UserProgressScore extends Model
{
    /** @use HasFactory<UserProgressScoreFactory> */
    use HasFactory;

    /**
     * Average score as a percentage of each play's max_score, rounded to 1 decimal. Never divides by zero:
     * user_progress_scores_score_check guarantees max_score > 0. NULL when there are no rows.
     */
    public static function averagePercentSql(string $alias = 'ups'): string
    {
        return "ROUND(AVG({$alias}.score * 100.0 / {$alias}.max_score), 1)";
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'max_score' => 'integer',
            'duration_seconds' => 'integer',
            'played_at' => 'datetime',
            'details_json' => AsArrayObject::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<GameModule, $this>
     */
    public function gameModule(): BelongsTo
    {
        return $this->belongsTo(GameModule::class, 'games_module_id');
    }

    /**
     * @return BelongsTo<ConfigVersion, $this>
     */
    public function configVersion(): BelongsTo
    {
        return $this->belongsTo(ConfigVersion::class);
    }
}
