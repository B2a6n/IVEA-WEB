<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\EducandoFiltersData;
use App\Enums\EducandoStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filters shared by the directory listing and its CSV export, so both always return the same educandos.
 */
final class EducandoDirectoryRequest extends FormRequest
{
    private const int MAX_SEARCH_LENGTH = 100;

    // A GET form: on bad input go back to the unfiltered directory, never to the same failing URL.
    protected $redirectRoute = 'educandos.index';

    public function authorize(): bool
    {
        // Access is enforced by the auth + role:admin,tutor route middleware.
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:'.self::MAX_SEARCH_LENGTH],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'status' => ['nullable', Rule::enum(EducandoStatus::class)],
        ];
    }

    public function toFilters(): EducandoFiltersData
    {
        $search = trim((string) $this->validated('search'));
        $groupId = $this->validated('group_id');
        $status = $this->validated('status');

        return new EducandoFiltersData(
            search: $search === '' ? null : $search,
            groupId: $groupId === null ? null : (int) $groupId,
            status: $status === null ? EducandoStatus::DEFAULT : EducandoStatus::from($status),
        );
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['search' => 'búsqueda', 'group_id' => 'grupo regional', 'status' => 'estado'];
    }
}
