<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Desired is_active state per games_modules id, already validated by UpdateModulesRequest.
 */
final readonly class ModuleActivationData
{
    /**
     * @param  array<int, bool>  $states
     * @param  int|null  $baseVersion  Highest config version when the form was rendered (null: none existed).
     */
    public function __construct(
        public array $states,
        public ?int $baseVersion,
    ) {}
}
