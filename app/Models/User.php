<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// role is intentionally not fillable: it must be assigned explicitly, never from request input.
#[Fillable(['name', 'email', 'password', 'group_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Group, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return HasMany<Group, $this>
     */
    public function tutoredGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'tutor_id');
    }

    /**
     * @return HasMany<UserProgressScore, $this>
     */
    public function progressScores(): HasMany
    {
        return $this->hasMany(UserProgressScore::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isTutor(): bool
    {
        return $this->role === UserRole::Tutor;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function canAccessWebPortal(): bool
    {
        return $this->is_active && in_array($this->role?->value, UserRole::webPortalValues(), true);
    }

    /**
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function tutors(Builder $query): void
    {
        $query->where('role', UserRole::Tutor);
    }

    /**
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function students(Builder $query): void
    {
        $query->where('role', UserRole::Student);
    }
}
