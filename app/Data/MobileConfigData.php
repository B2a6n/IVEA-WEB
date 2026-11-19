<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use Illuminate\Support\Collection;

final readonly class MobileConfigData
{
    /**
     * @param  Collection<int, GameModule>  $modules
     */
    public function __construct(
        public ?ConfigVersion $configVersion,
        public Collection $modules,
    ) {}
}
