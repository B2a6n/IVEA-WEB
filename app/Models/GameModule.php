<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GameModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'title', 'description', 'subject_area', 'difficulty_level', 'sort_order', 'is_active'])]
class GameModule extends Model
{
    /** @use HasFactory<GameModuleFactory> */
    use HasFactory;

    protected $table = 'games_modules';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty_level' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<UserProgressScore, $this>
     */
    public function progressScores(): HasMany
    {
        return $this->hasMany(UserProgressScore::class, 'games_module_id');
    }

    /**
     * @param  Builder<GameModule>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
