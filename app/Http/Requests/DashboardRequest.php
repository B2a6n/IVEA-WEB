<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\DashboardFiltersData;
use App\Enums\DashboardPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DashboardRequest extends FormRequest
{
    // A GET form: on bad input go back to the unfiltered page, never to the same failing URL.
    protected $redirectRoute = 'dashboard';

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
            'module_id' => ['nullable', 'integer', 'exists:games_modules,id'],
            'group_id' => ['nullable', 'integer', 'exists:groups,id'],
            'period' => ['nullable', Rule::enum(DashboardPeriod::class)],
        ];
    }

    public function toFilters(): DashboardFiltersData
    {
        $moduleId = $this->validated('module_id');
        $groupId = $this->validated('group_id');
        $period = $this->validated('period');

        return new DashboardFiltersData(
            moduleId: $moduleId === null ? null : (int) $moduleId,
            groupId: $groupId === null ? null : (int) $groupId,
            period: $period === null ? DashboardPeriod::DEFAULT : DashboardPeriod::from($period),
        );
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['module_id' => 'módulo', 'group_id' => 'grupo regional', 'period' => 'periodo'];
    }
}
