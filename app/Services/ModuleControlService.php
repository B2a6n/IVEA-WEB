<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ModuleControlData;
use App\Models\ConfigVersion;
use App\Models\GameModule;

final class ModuleControlService
{
    public function overview(): ModuleControlData
    {
        $modules = GameModule::query()
            ->withCount('progressScores as user_progress_scores_count')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $latestVersion = ConfigVersion::query()->max('version');

        return new ModuleControlData(
            modules: $modules,
            currentVersion: ConfigVersion::current(),
            latestVersion: $latestVersion === null ? null : (int) $latestVersion,
            activeCount: $modules->where('is_active', true)->count(),
        );
    }
}
