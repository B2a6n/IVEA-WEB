<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class EducandoDirectoryData
{
    /**
     * @param  LengthAwarePaginator<int, EducandoRowData>  $students
     * @param  array<int, string>  $groupOptions  groups id => "Zona · Nombre".
     * @param  int  $activeModules  Denominator of each row's module coverage.
     */
    public function __construct(
        public EducandoFiltersData $filters,
        public LengthAwarePaginator $students,
        public array $groupOptions,
        public int $activeModules,
    ) {}
}
