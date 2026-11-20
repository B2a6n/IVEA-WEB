<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * The reset token was invalid, expired, already used, or did not belong to an active portal account. One
 * message covers every case so the form cannot be used to probe accounts.
 */
final class PasswordResetFailedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('El enlace de restablecimiento no es válido o ha expirado.');
    }
}
