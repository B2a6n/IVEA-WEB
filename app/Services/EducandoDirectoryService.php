<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\EducandoDetailData;
use App\Data\EducandoDirectoryData;
use App\Data\EducandoFiltersData;
use App\Data\EducandoRowData;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EducandoDirectoryService
{
    public const int PER_PAGE = 15;

    public const int RECENT_PLAYS = 15;

    public function directory(EducandoFiltersData $filters): EducandoDirectoryData
    {
        $students = $this->query($filters)
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (User $user) => $this->toRow($user));

        return new EducandoDirectoryData(
            filters: $filters,
            students: $students,
            groupOptions: Group::selectOptions(),
            activeModules: GameModule::query()->active()->count(),
        );
    }

    /**
     * Students matching the filters, each with its play aggregates (one grouped subquery, no N+1).
     *
     * @return Builder<User>
     */
    public function query(EducandoFiltersData $filters): Builder
    {
        $stats = DB::table('user_progress_scores as ups')
            ->join('games_modules as gm', 'gm.id', '=', 'ups.games_module_id')
            ->select('ups.user_id')
            ->selectRaw('COUNT(*) AS activities')
            // Coverage is measured against the modules currently enabled in the app.
            ->selectRaw('COUNT(DISTINCT ups.games_module_id) FILTER (WHERE gm.is_active) AS modules_played')
            ->selectRaw(UserProgressScore::averagePercentSql().' AS average_score')
            ->selectRaw('MAX(ups.played_at) AS last_played_at')
            ->groupBy('ups.user_id');

        $isActive = $filters->status->isActive();

        return User::query()
            ->students()
            ->leftJoinSub($stats, 'stats', 'stats.user_id', '=', 'users.id')
            ->select('users.*')
            ->selectRaw('COALESCE(stats.activities, 0) AS activities')
            ->selectRaw('COALESCE(stats.modules_played, 0) AS modules_played')
            ->addSelect('stats.average_score', 'stats.last_played_at')
            ->with('group:id,name,zone')
            ->when($filters->search !== null, function (Builder $query) use ($filters): void {
                // Escape LIKE wildcards so the search text is matched literally.
                $pattern = '%'.addcslashes((string) $filters->search, '\\%_').'%';
                $query->where(fn (Builder $where) => $where
                    ->where('users.name', 'ilike', $pattern)
                    ->orWhere('users.email', 'ilike', $pattern));
            })
            ->when($filters->groupId !== null, fn (Builder $q) => $q->where('users.group_id', $filters->groupId))
            ->when($isActive !== null, fn (Builder $q) => $q->where('users.is_active', $isActive))
            ->orderBy('users.name')
            ->orderBy('users.id');
    }

    /**
     * @param  User  $user  A row of query(), carrying the aggregate columns.
     */
    public function toRow(User $user): EducandoRowData
    {
        $average = $user->getAttribute('average_score');
        $lastPlayedAt = $user->getAttribute('last_played_at');

        return new EducandoRowData(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            group: $user->group?->name,
            zone: $user->group?->zone,
            isActive: $user->is_active,
            activities: (int) $user->getAttribute('activities'),
            modulesPlayed: (int) $user->getAttribute('modules_played'),
            averageScore: $average === null ? null : (float) $average,
            lastActivity: $lastPlayedAt === null ? null : $this->localDate($lastPlayedAt, 'd/m/Y'),
        );
    }

    /**
     * @param  User  $user  A student (enforced by UserPolicy::viewEducando on the routes).
     * @param  int  $historyLimit  Latest plays to include: the slide-over shows 15, the PDF more.
     */
    public function detail(User $user, int $historyLimit = self::RECENT_PLAYS): EducandoDetailData
    {
        $row = $this->toRow($this->query(new EducandoFiltersData)->whereKey($user->id)->firstOrFail());
        $recent = $this->recentPlays($user, $historyLimit);

        return new EducandoDetailData(
            student: $row,
            activeModules: GameModule::query()->active()->count(),
            lastActivity: $recent[0]['played_at'] ?? null,
            recent: $recent,
            chart: $this->moduleChart($user),
            pdfUrl: route('educandos.pdf', $user),
        );
    }

    /**
     * @return list<array{module: string, played_at: string, score: int, max_score: int, percent: float}>
     */
    private function recentPlays(User $user, int $limit): array
    {
        return DB::table('user_progress_scores as ups')
            ->join('games_modules as gm', 'gm.id', '=', 'ups.games_module_id')
            ->where('ups.user_id', $user->id)
            ->orderByDesc('ups.played_at')
            ->orderByDesc('ups.id')
            ->limit($limit)
            ->get(['gm.title', 'ups.played_at', 'ups.score', 'ups.max_score'])
            ->map(fn (object $play) => [
                'module' => $play->title,
                'played_at' => $this->localDate($play->played_at, 'd/m/Y H:i'),
                'score' => (int) $play->score,
                'max_score' => (int) $play->max_score,
                // max_score > 0 is guaranteed by user_progress_scores_score_check.
                'percent' => round($play->score * 100 / $play->max_score, 1),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array{labels: list<string>, average: list<float>, plays: list<int>}
     */
    private function moduleChart(User $user): array
    {
        $rows = DB::table('user_progress_scores as ups')
            ->join('games_modules as gm', 'gm.id', '=', 'ups.games_module_id')
            ->where('ups.user_id', $user->id)
            ->select('gm.title')
            ->selectRaw(UserProgressScore::averagePercentSql().' AS average')
            ->selectRaw('COUNT(*) AS plays')
            ->groupBy('gm.id', 'gm.title', 'gm.sort_order')
            ->orderBy('gm.sort_order')
            ->orderBy('gm.id')
            ->get();

        return [
            'labels' => $rows->pluck('title')->all(),
            'average' => $rows->map(fn (object $row) => (float) $row->average)->all(),
            'plays' => $rows->map(fn (object $row) => (int) $row->plays)->all(),
        ];
    }

    private function localDate(string $timestamp, string $format): string
    {
        return Carbon::parse($timestamp)->setTimezone((string) config('ivea.reporting_timezone'))->format($format);
    }
}
