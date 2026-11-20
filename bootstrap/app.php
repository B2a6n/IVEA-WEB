<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureWebRole;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureWebRole::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // API v1 errors share the {success, data, error, meta} envelope and never expose internals.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof ValidationException => ApiResponse::error(
                    'validation_error', 'The given data was invalid.', $e->status, $e->errors(),
                ),
                $e instanceof AuthenticationException => ApiResponse::error(
                    'unauthenticated', 'Unauthenticated.', 401,
                ),
                $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => ApiResponse::error(
                    'forbidden', 'This action is unauthorized.', 403,
                ),
                $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => ApiResponse::error(
                    'not_found', 'Resource not found.', 404,
                ),
                $e instanceof ThrottleRequestsException => ApiResponse::error(
                    'rate_limit_exceeded', 'Too many requests.', 429,
                )->withHeaders($e->getHeaders()),
                $e instanceof HttpExceptionInterface => ApiResponse::error(
                    'http_error', 'The request could not be processed.', $e->getStatusCode(),
                )->withHeaders($e->getHeaders()),
                default => config('app.debug') ? null : ApiResponse::error(
                    'server_error', 'Internal server error.', 500,
                ),
            };
        });
    })->create();
