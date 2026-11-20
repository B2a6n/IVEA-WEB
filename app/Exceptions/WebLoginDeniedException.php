<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A web portal login was refused. The message is user-facing (Spanish) and safe to show: the inactive and
 * student variants are only raised after the password has been verified.
 */
final class WebLoginDeniedException extends RuntimeException
{
    public static function invalidCredentials(): self
    {
        return new self('Las credenciales proporcionadas no coinciden con nuestros registros.');
    }

    public static function inactiveAccount(): self
    {
        return new self('Tu cuenta está desactivada. Contacta a la Mesa Técnica IVEA.');
    }

    public static function notPortalRole(): self
    {
        return new self(
            'El portal web es exclusivo para administradores y tutores. '
            .'Los educandos deben usar la aplicación móvil IVEA.'
        );
    }
}
