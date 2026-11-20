<?php

declare(strict_types=1);

namespace App\Http\Requests\Modules;

use App\Data\ModuleActivationData;
use App\Models\GameModule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateModulesRequest extends FormRequest
{
    // Far above the real catalog size; bounds per-item validation and the IN list of the write.
    private const int MAX_MODULES = 200;

    public function authorize(): bool
    {
        return $this->user()?->can('manage', GameModule::class) ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'modules' => ['required', 'array', 'min:1', 'max:'.self::MAX_MODULES],
            'modules.*' => ['required', 'boolean'],
            'base_version' => ['present', 'nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Keys of `modules` are games_modules ids: every one must exist, or nothing is saved.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $modules = $this->input('modules');

                if ($validator->errors()->isNotEmpty() || ! is_array($modules)) {
                    return;
                }

                $ids = array_keys($modules);
                $allIntegers = array_all($ids, fn (int|string $id) => is_int($id));

                if (! $allIntegers || GameModule::query()->whereKey($ids)->count() !== count($ids)) {
                    $validator->errors()->add('modules', 'Uno o más módulos no existen.');
                }
            },
        ];
    }

    public function toData(): ModuleActivationData
    {
        $states = [];

        foreach ($this->validated('modules') as $id => $isActive) {
            $states[(int) $id] = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
        }

        $baseVersion = $this->validated('base_version');

        return new ModuleActivationData($states, $baseVersion === null ? null : (int) $baseVersion);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['modules' => 'módulos', 'modules.*' => 'estado del módulo', 'base_version' => 'versión base'];
    }
}
