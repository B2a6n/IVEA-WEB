<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Pedagogical band of an educando's average score (percent of max score).
 */
enum PerformanceLevel: string
{
    case Excellent = 'excellent';
    case OnTrack = 'on_track';
    case Review = 'review';
    case NeedsSupport = 'needs_support';
    case NoPlays = 'no_plays';

    // Below this average the educando is referred to in-person reinforcement sessions.
    public const int SUPPORT_THRESHOLD = 60;

    private const int EXCELLENT_THRESHOLD = 90;

    private const int ON_TRACK_THRESHOLD = 70;

    public static function fromAverage(?float $average): self
    {
        return match (true) {
            $average === null => self::NoPlays,
            $average >= self::EXCELLENT_THRESHOLD => self::Excellent,
            $average >= self::ON_TRACK_THRESHOLD => self::OnTrack,
            $average >= self::SUPPORT_THRESHOLD => self::Review,
            default => self::NeedsSupport,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excelente',
            self::OnTrack => 'Regular',
            self::Review => 'Refuerzo',
            self::NeedsSupport => 'Requiere apoyo',
            self::NoPlays => 'Sin partidas',
        };
    }

    /**
     * Tone of <x-ui.badge>.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Excellent => 'success',
            self::OnTrack => 'info',
            self::Review => 'warning',
            self::NeedsSupport => 'danger',
            self::NoPlays => 'neutral',
        };
    }

    /**
     * Fill level of <x-ui.progress>.
     */
    public function progressLevel(): string
    {
        return match ($this) {
            self::Excellent => 'mastered',
            self::Review => 'review',
            self::NeedsSupport => 'risk',
            self::OnTrack, self::NoPlays => 'on-track',
        };
    }
}
