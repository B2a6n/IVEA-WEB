<?php

declare(strict_types=1);

namespace App\Data;

final readonly class DashboardData
{
    /**
     * @param  array{labels: list<string>, average: list<float>, volume: list<int>}  $trendChart  One point per day.
     * @param  array{labels: list<string>, average: list<float|null>, plays: list<int>}  $moduleChart  Null = no plays.
     * @param  array<int, string>  $moduleOptions  games_modules id => title.
     * @param  array<int, string>  $groupOptions  groups id => "Zona · Nombre".
     */
    public function __construct(
        public DashboardFiltersData $filters,
        public int $activeStudents,
        public int $completedActivities,
        public float $averageScore,
        public array $trendChart,
        public array $moduleChart,
        public array $moduleOptions,
        public array $groupOptions,
    ) {}

    public function hasData(): bool
    {
        return $this->completedActivities > 0;
    }
}
