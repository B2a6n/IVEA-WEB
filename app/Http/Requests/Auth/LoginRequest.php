<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Data\LoginCredentialsData;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    private const int MAX_ATTEMPTS = 5;

    // Per-IP ceiling across all emails, against password spraying.
    private const int MAX_ATTEMPTS_PER_IP = 20;

    private const int DECAY_SECONDS = 60;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function toData(): LoginCredentialsData
    {
        return new LoginCredentialsData(
            email: $this->string('email')->trim()->toString(),
            password: $this->string('password')->toString(),
            remember: $this->boolean('remember'),
        );
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $limits = [$this->throttleKey() => self::MAX_ATTEMPTS, $this->ipThrottleKey() => self::MAX_ATTEMPTS_PER_IP];

        foreach ($limits as $key => $maxAttempts) {
            if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                continue;
            }

            event(new Lockout($this));

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }
    }

    public function recordFailedAttempt(): void
    {
        RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);
        RateLimiter::hit($this->ipThrottleKey(), self::DECAY_SECONDS);
    }

    public function clearFailedAttempts(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->trim()->toString()).'|'.$this->ip());
    }

    private function ipThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }
}
