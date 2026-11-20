<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\EducandoStatus;

final readonly class EducandoFiltersData
{
    public function __construct(
        public ?string $search = null,
        public ?int $groupId = null,
        public EducandoStatus $status = EducandoStatus::DEFAULT,
    ) {}

    public function isFiltered(): bool
    {
        return $this->toQuery() !== [];
    }

    /**
     * Active filters as query-string parameters (defaults omitted), e.g. for the export link.
     *
     * @return array<string, int|string>
     */
    public function toQuery(): array
    {
        return array_filter([
            'search' => $this->search,
            'group_id' => $this->groupId,
            'status' => $this->status === EducandoStatus::DEFAULT ? null : $this->status->value,
        ], fn (int|string|null $value) => $value !== null);
    }
}
