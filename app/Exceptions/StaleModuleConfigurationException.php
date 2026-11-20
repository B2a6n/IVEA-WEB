<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * The form was built on a configuration version that another admin has since replaced; saving it would
 * silently revert their changes.
 */
final class StaleModuleConfigurationException extends RuntimeException
{
    public static function since(?int $baseVersion, int $latestVersion): self
    {
        return new self(sprintf(
            'Otro administrador publicó la versión v%d mientras editabas%s. Revisa los módulos y vuelve a guardar.',
            $latestVersion,
            $baseVersion === null ? '' : " (partías de la v{$baseVersion})",
        ));
    }
}
