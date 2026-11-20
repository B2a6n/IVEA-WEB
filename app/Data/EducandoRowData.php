<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PerformanceLevel;

/**
 * One educando with its aggregated play metrics, as listed in the directory and the CSV export.
 */
final readonly class EducandoRowData
{
    public PerformanceLevel $level;

    /**
     * @param  float|null  $averageScore  Null when the educando has no plays.
     * @param  string|null  $lastActivity  Local date (d/m/Y) of the latest play.
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $group,
        public ?string $zone,
        public bool $isActive,
        public int $activities,
        public int $modulesPlayed,
        public ?float $averageScore,
        public ?string $lastActivity,
    ) {
        $this->level = PerformanceLevel::fromAverage($averageScore);
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1));
    }

    public function statusLabel(): string
    {
        return $this->isActive ? 'Activo' : 'Inactivo';
    }

    public function averageLabel(): string
    {
        return number_format($this->averageScore ?? 0, 1);
    }
}
