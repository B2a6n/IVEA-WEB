<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'zone', 'tutor_id', 'is_active'])]
class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    public const array ZONES = ['Xalapa', 'Veracruz', 'Córdoba'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Options for a group filter select, ordered by zone.
     *
     * @return array<int, string> id => "Zona · Nombre"
     */
    public static function selectOptions(): array
    {
        return self::query()
            ->orderBy('zone')
            ->orderBy('name')
            ->get(['id', 'zone', 'name'])
            ->mapWithKeys(fn (self $group) => [$group->id => "{$group->zone} · {$group->name}"])
            ->all();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
