<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

/**
 * One offline game result as reported by the mobile app.
 */
final readonly class ScoreRecordData
{
    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        public string $clientUuid,
        public string $gameSlug,
        public int $score,
        public int $maxScore,
        public int $durationSeconds,
        public CarbonImmutable $playedAt,
        public ?array $details,
    ) {}
}
