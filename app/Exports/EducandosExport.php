<?php

declare(strict_types=1);

namespace App\Exports;

use App\Data\EducandoFiltersData;
use App\Models\User;
use App\Services\EducandoDirectoryService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * The directory as CSV (UTF-8 with BOM so Excel reads the accents), with the same filters as the listing.
 *
 * @implements WithMapping<User>
 */
final class EducandosExport implements FromQuery, WithCustomCsvSettings, WithHeadings, WithMapping
{
    // Leading characters a spreadsheet would evaluate as a formula (CSV injection).
    private const string FORMULA_PREFIXES = "=+-@\t\r\n";

    public function __construct(
        private readonly EducandoDirectoryService $service,
        private readonly EducandoFiltersData $filters,
    ) {}

    /**
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return $this->service->query($this->filters);
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nombre', 'Correo', 'Grupo', 'Zona', 'Estado', 'Actividades', 'Módulos jugados', 'Puntaje promedio',
            'Última actividad',
        ];
    }

    /**
     * @param  User  $row
     * @return list<int|string>
     */
    public function map($row): array
    {
        $educando = $this->service->toRow($row);

        return [
            $this->cell($educando->name),
            $this->cell($educando->email),
            $this->cell($educando->group ?? ''),
            $this->cell($educando->zone ?? ''),
            $educando->statusLabel(),
            $educando->activities,
            $educando->modulesPlayed,
            $educando->averageLabel(),
            $educando->lastActivity ?? '',
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function getCsvSettings(): array
    {
        return ['use_bom' => true];
    }

    private function cell(string $value): string
    {
        return $value !== '' && str_contains(self::FORMULA_PREFIXES, $value[0]) ? "'".$value : $value;
    }
}
