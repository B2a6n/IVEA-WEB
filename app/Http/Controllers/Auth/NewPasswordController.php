<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetUserPasswordAction;
use App\Exceptions\PasswordResetFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(ResetPasswordRequest $request, ResetUserPasswordAction $resetPassword): RedirectResponse
    {
        try {
            $resetPassword->execute($request->toData());
        } catch (PasswordResetFailedException $e) {
            throw ValidationException::withMessages(['email' => $e->getMessage()]);
        }

        return redirect()->route('login')
            ->with('status', 'Tu contraseña fue restablecida. Ya puedes iniciar sesión.');
    }
}
