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
