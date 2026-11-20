<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\MobileConfigData;
use App\Models\ConfigVersion;
use App\Models\GameModule;

final class MobileConfigService
{
    // Invalidated whenever a new configuration is published; reserved for caching current().
    public const string CACHE_KEY = 'mobile-config.current';

    private const array MODULE_COLUMNS = ['slug', 'title', 'description', 'subject_area', 'difficulty_level'];

    public function current(): MobileConfigData
    {
        return new MobileConfigData(
            configVersion: ConfigVersion::current(),
            modules: GameModule::query()->active()->orderBy('sort_order')->orderBy('id')->get(self::MODULE_COLUMNS),
        );
    }
}
