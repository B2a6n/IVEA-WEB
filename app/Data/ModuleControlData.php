<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use Illuminate\Support\Collection;

final readonly class ModuleControlData
{
    /**
     * @param  Collection<int, GameModule>  $modules  Each with user_progress_scores_count loaded.
     * @param  int|null  $latestVersion  Highest version number, drafts included; the form's base_version.
     */
    public function __construct(
        public Collection $modules,
        public ?ConfigVersion $currentVersion,
        public ?int $latestVersion,
        public int $activeCount,
    ) {}
}
