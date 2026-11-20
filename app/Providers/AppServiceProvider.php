<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    private const int API_REQUESTS_PER_MINUTE = 60;

    private const int PASSWORD_RESET_REQUESTS_PER_MINUTE = 5;

    private const int PASSWORD_MIN_LENGTH = 10;

    // Directorio de Educandos: the detail feeds the slide-over; CSV and PDF are expensive to build.
    private const array EDUCANDO_LIMITS_PER_MINUTE = [
        'educandos-detail' => 60,
        'educandos-export' => 10,
        'educandos-pdf' => 20,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Any N+1 lazy load fails loudly in local and tests; production only skips the guard.
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(self::API_REQUESTS_PER_MINUTE)
            ->by($request->user()?->id ?: $request->ip()));

        // Per IP and per target email, so one client cannot flood a single inbox or sweep many addresses.
        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(self::PASSWORD_RESET_REQUESTS_PER_MINUTE)->by('ip:'.$request->ip()),
            Limit::perMinute(self::PASSWORD_RESET_REQUESTS_PER_MINUTE)
                ->by('email:'.Str::lower($request->string('email')->trim()->toString())),
        ]);

        // Named so each endpoint has its own counter: anonymous throttle:N,M middleware share one key per user.
        foreach (self::EDUCANDO_LIMITS_PER_MINUTE as $name => $perMinute) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute($perMinute)
                ->by($request->user()?->id ?: $request->ip()));
        }

        // Security audit trail; never log passwords or reset tokens.
        Event::listen(Lockout::class, fn (Lockout $event) => Log::warning('auth.lockout', [
            'email' => $event->request->string('email')->toString(),
            'ip' => $event->request->ip(),
        ]));
        Event::listen(PasswordReset::class, fn (PasswordReset $event) => Log::notice('auth.password_reset', [
            'user_id' => $event->user->getAuthIdentifier(),
            'ip' => request()->ip(),
        ]));

        // resources/views/vendor/pagination/ivea.blade.php: tokens only, unlike the bundled views.
        Paginator::defaultView('pagination::ivea');

        Password::defaults(function (): Password {
            $rule = Password::min(self::PASSWORD_MIN_LENGTH)->letters()->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
