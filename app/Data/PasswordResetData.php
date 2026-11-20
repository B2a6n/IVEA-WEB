<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Password reset submission, already validated by ResetPasswordRequest.
 */
final readonly class PasswordResetData
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
    ) {}
}
