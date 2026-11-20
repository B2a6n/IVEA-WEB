<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonImmutable;

enum DashboardPeriod: string
{
    case SevenDays = '7d';
    case ThirtyDays = '30d';
    case NinetyDays = '90d';
    case All = 'all';

    public const self DEFAULT = self::ThirtyDays;

    public function label(): string
    {
        return match ($this) {
            self::SevenDays => 'Últimos 7 días',
            self::ThirtyDays => 'Últimos 30 días',
            self::NinetyDays => 'Últimos 90 días',
            self::All => 'Todo el historial',
        };
    }

    /**
     * Start of the window (inclusive, at the start of that day in $timezone); null means no lower bound.
     */
    public function since(string $timezone): ?CarbonImmutable
    {
        $days = match ($this) {
            self::SevenDays => 7,
            self::ThirtyDays => 30,
            self::NinetyDays => 90,
            self::All => null,
        };

        return $days === null ? null : CarbonImmutable::now($timezone)->subDays($days)->startOfDay();
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $period) => $period->label(), self::cases()),
        );
    }
}
