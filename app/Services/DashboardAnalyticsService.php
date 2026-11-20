<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\DashboardData;
use App\Data\DashboardFiltersData;
use App\Enums\UserRole;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\UserProgressScore;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DashboardAnalyticsService
{
    public function overview(DashboardFiltersData $filters): DashboardData
    {
        $kpis = $this->scopedScores($filters)
            ->selectRaw('COUNT(DISTINCT ups.user_id) AS active_students')
            ->selectRaw('COUNT(*) AS completed_activities')
            ->selectRaw(UserProgressScore::averagePercentSql().' AS average_score')
            ->first();

        $moduleOptions = GameModule::query()->orderBy('sort_order')->orderBy('id')->pluck('title', 'id')->all();

        return new DashboardData(
            filters: $filters,
            activeStudents: (int) $kpis->active_students,
            completedActivities: (int) $kpis->completed_activities,
            // AVG over zero rows is NULL: report 0.0 instead.
            averageScore: (float) ($kpis->average_score ?? 0),
            trendChart: $this->trendChart($filters),
            moduleChart: $this->moduleChart($filters, $moduleOptions),
            moduleOptions: $moduleOptions,
            groupOptions: Group::selectOptions(),
        );
    }

    /**
     * Plays by students only (staff test games are excluded), narrowed by the active filters.
     */
    private function scopedScores(DashboardFiltersData $filters): Builder
    {
        $since = $filters->period->since($this->timezone());

        return DB::table('user_progress_scores as ups')
            ->join('users', 'users.id', '=', 'ups.user_id')
            ->where('users.role', UserRole::Student->value)
            ->when($filters->moduleId !== null, fn (Builder $q) => $q->where('ups.games_module_id', $filters->moduleId))
            ->when($filters->groupId !== null, fn (Builder $q) => $q->where('users.group_id', $filters->groupId))
            // Bound without an offset, so it must be in the database session's timezone (UTC).
            ->when($since !== null, fn (Builder $q) => $q->where('ups.played_at', '>=', $since->utc()));
    }

    /**
     * @return array{labels: list<string>, average: list<float>, volume: list<int>}
     */
    private function trendChart(DashboardFiltersData $filters): array
    {
        // Bucket by the educandos' local calendar day, not the database session's (UTC).
        $rows = $this->scopedScores($filters)
            ->selectRaw('(ups.played_at AT TIME ZONE ?)::date AS day', [$this->timezone()])
            ->selectRaw(UserProgressScore::averagePercentSql().' AS average')
            ->selectRaw('COUNT(*) AS plays')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return [
            'labels' => $rows->map(fn (object $row) => Carbon::parse($row->day)->format('d/m'))->all(),
            'average' => $rows->map(fn (object $row) => (float) $row->average)->all(),
            'volume' => $rows->map(fn (object $row) => (int) $row->plays)->all(),
        ];
    }

    /**
     * @param  array<int, string>  $moduleOptions
     * @return array{labels: list<string>, average: list<float|null>, plays: list<int>}
     */
    private function moduleChart(DashboardFiltersData $filters, array $moduleOptions): array
    {
        $stats = $this->scopedScores($filters)
            ->select('ups.games_module_id')
            ->selectRaw(UserProgressScore::averagePercentSql().' AS average')
            ->selectRaw('COUNT(*) AS plays')
            ->groupBy('ups.games_module_id')
            ->get()
            ->keyBy('games_module_id');

        $modules = $filters->moduleId === null
            ? $moduleOptions
            : array_intersect_key($moduleOptions, [$filters->moduleId => true]);

        $chart = ['labels' => [], 'average' => [], 'plays' => []];

        foreach ($modules as $id => $title) {
            $row = $stats->get($id);
            $chart['labels'][] = $title;
            $chart['average'][] = $row === null ? null : (float) $row->average;
            $chart['plays'][] = $row === null ? 0 : (int) $row->plays;
        }

        return $chart;
    }

    private function timezone(): string
    {
        return (string) config('ivea.reporting_timezone');
    }
}
