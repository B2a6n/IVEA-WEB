<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Tutor = 'tutor';
    case Student = 'student';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Roles allowed into the web portal; students only use the mobile app.
     *
     * @return list<string>
     */
    public static function webPortalValues(): array
    {
        return [self::Admin->value, self::Tutor->value];
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Tutor => 'Tutor',
            self::Student => 'Educando',
        };
    }
}
