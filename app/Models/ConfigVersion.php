<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ConfigVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['version', 'payload', 'notes', 'published_by', 'published_at'])]
class ConfigVersion extends Model
{
    /** @use HasFactory<ConfigVersionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'payload' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The configuration currently served to the mobile app: the highest published version.
     */
    public static function current(): ?self
    {
        return self::query()->published()->orderByDesc('version')->first();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * @return HasMany<UserProgressScore, $this>
     */
    public function progressScores(): HasMany
    {
        return $this->hasMany(UserProgressScore::class);
    }

    /**
     * @param  Builder<ConfigVersion>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
