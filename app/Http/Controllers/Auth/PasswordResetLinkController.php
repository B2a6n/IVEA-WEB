<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordRequest $request, SendPasswordResetLinkAction $sendLink): RedirectResponse
    {
        $sendLink->execute($request->email());

        return back()->with(
            'status',
            'Si el correo pertenece a una cuenta activa del portal, '
                .'recibirás un enlace para restablecer tu contraseña.',
        );
    }
}
