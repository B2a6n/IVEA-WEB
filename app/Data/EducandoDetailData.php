<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Payload of the slide-over (JSON) and of the PDF record.
 */
final readonly class EducandoDetailData
{
    /**
     * @param  list<array{module: string, played_at: string, score: int, max_score: int, percent: float}>  $recent
     * @param  array{labels: list<string>, average: list<float>, plays: list<int>}  $chart  Per module played.
     * @param  string|null  $lastActivity  Local date and time of the newest play ($recent is newest first).
     */
    public function __construct(
        public EducandoRowData $student,
        public int $activeModules,
        public ?string $lastActivity,
        public array $recent,
        public array $chart,
        public string $pdfUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $student = $this->student;

        return [
            'profile' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'initial' => $student->initial(),
                'group' => $student->group,
                'zone' => $student->zone,
                'status' => $student->statusLabel(),
            ],
            'kpis' => [
                'activities' => $student->activities,
                'average_score' => $student->averageScore ?? 0.0,
                'modules_played' => $student->modulesPlayed,
                'active_modules' => $this->activeModules,
                'last_activity' => $this->lastActivity,
                'level' => $student->level->label(),
            ],
            'recent' => $this->recent,
            'chart' => $this->chart,
            'pdf_url' => $this->pdfUrl,
        ];
    }
}
