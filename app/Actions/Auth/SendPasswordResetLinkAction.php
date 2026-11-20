<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Password;

final class SendPasswordResetLinkAction
{
    /**
     * Sends a reset link only to active admins and tutors. The broker status is deliberately not returned:
     * callers always show the same answer so the form cannot be used to discover accounts.
     */
    public function execute(string $email): void
    {
        Password::broker()->sendResetLink([
            'email' => $email,
            'is_active' => true,
            'role' => UserRole::webPortalValues(),
        ]);
    }
}
