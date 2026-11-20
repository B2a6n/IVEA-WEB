<?php

declare(strict_types=1);

namespace App\Enums;

enum EducandoStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case All = 'all';

    public const self DEFAULT = self::All;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activos',
            self::Inactive => 'Inactivos',
            self::All => 'Todos los estados',
        };
    }

    /**
     * Value of users.is_active to filter by; null means no filter.
     */
    public function isActive(): ?bool
    {
        return match ($this) {
            self::Active => true,
            self::Inactive => false,
            self::All => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $status) => $status->label(), self::cases()),
        );
    }
}
