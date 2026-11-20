<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\DashboardPeriod;

final readonly class DashboardFiltersData
{
    public function __construct(
        public ?int $moduleId = null,
        public ?int $groupId = null,
        public DashboardPeriod $period = DashboardPeriod::DEFAULT,
    ) {}

    public function isFiltered(): bool
    {
        return $this->moduleId !== null || $this->groupId !== null || $this->period !== DashboardPeriod::DEFAULT;
    }
}
