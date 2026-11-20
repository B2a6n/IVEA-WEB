<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\WebLoginDeniedException;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web RBAC: `role:admin,tutor` for read screens, `role:admin` for anything that writes. Tutors are read-only
 * everywhere, so a write route placed by mistake in a shared group still answers 403 to them.
 */
final class EnsureWebRole
{
    private const array SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->is_active) {
            return $this->logOutDeactivated($request);
        }

        if (! in_array($user->role?->value, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder a esta sección.');
        }

        if ($user->isTutor() && ! in_array($request->method(), self::SAFE_METHODS, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Tu rol de tutor es de solo lectura.');
        }

        return $next($request);
    }

    private function logOutDeactivated(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->withErrors(['email' => WebLoginDeniedException::inactiveAccount()->getMessage()]);
    }
}
