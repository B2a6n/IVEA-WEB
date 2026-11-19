<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ScoreRecordData;
use App\Data\SyncBatchData;
use App\Data\SyncRecordResult;
use App\Data\SyncResultData;
use App\Enums\SyncStatus;
use App\Models\GameModule;
use App\Models\UserProgressScore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class SyncProgressScoresAction
{
    /**
     * Stores an offline batch idempotently: client_uuid is unique, so a record that already
     * exists (from a previous sync or earlier in the same batch) is ignored and reported as duplicate.
     * A client_uuid already owned by another user is reported as rejected, never as synced.
     */
    public function execute(SyncBatchData $batch): SyncResultData
    {
        $moduleIds = $this->resolveModuleIds($batch->records);
        $now = CarbonImmutable::now();

        $results = DB::transaction(function () use ($batch, $moduleIds, $now): array {
            $results = array_map(
                fn (ScoreRecordData $record) => $this->syncRecord($batch->userId, $record, $moduleIds, $now),
                $batch->records,
            );

            return $this->rejectForeignDuplicates($batch->userId, $results);
        });

        return new SyncResultData($results);
    }

    /**
     * @param  list<SyncRecordResult>  $results
     * @return list<SyncRecordResult>
     */
    private function rejectForeignDuplicates(int $userId, array $results): array
    {
        $duplicateUuids = array_map(
            fn (SyncRecordResult $result) => $result->clientUuid,
            array_filter($results, fn (SyncRecordResult $result) => $result->status === SyncStatus::Duplicate),
        );

        if ($duplicateUuids === []) {
            return $results;
        }

        $owners = UserProgressScore::query()
            ->whereIn('client_uuid', array_values($duplicateUuids))
            ->pluck('user_id', 'client_uuid');

        return array_map(
            fn (SyncRecordResult $result) => $result->status === SyncStatus::Duplicate
                && (int) $owners->get($result->clientUuid) !== $userId
                    ? new SyncRecordResult(
                        $result->clientUuid,
                        SyncStatus::Rejected,
                        SyncRecordResult::REASON_UUID_CONFLICT,
                    )
                    : $result,
            $results,
        );
    }

    /**
     * @param  list<ScoreRecordData>  $records
     * @return array<string, int>
     */
    private function resolveModuleIds(array $records): array
    {
        $slugs = array_values(array_unique(array_map(fn (ScoreRecordData $record) => $record->gameSlug, $records)));

        return GameModule::query()->whereIn('slug', $slugs)->pluck('id', 'slug')->all();
    }

    /**
     * @param  array<string, int>  $moduleIds
     */
    private function syncRecord(
        int $userId,
        ScoreRecordData $record,
        array $moduleIds,
        CarbonImmutable $now,
    ): SyncRecordResult {
        $moduleId = $moduleIds[$record->gameSlug] ?? null;

        if ($moduleId === null) {
            return new SyncRecordResult(
                $record->clientUuid,
                SyncStatus::Rejected,
                SyncRecordResult::REASON_UNKNOWN_GAME_SLUG,
            );
        }

        // ON CONFLICT DO NOTHING: race-safe against concurrent syncs of the same record.
        $inserted = UserProgressScore::query()->insertOrIgnore([
            'client_uuid' => $record->clientUuid,
            'user_id' => $userId,
            'games_module_id' => $moduleId,
            'score' => $record->score,
            'max_score' => $record->maxScore,
            'duration_seconds' => $record->durationSeconds,
            'played_at' => $record->playedAt->utc()->toIso8601String(),
            'details_json' => $record->details === null ? null : json_encode($record->details, JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return new SyncRecordResult(
            $record->clientUuid,
            $inserted === 1 ? SyncStatus::Created : SyncStatus::Duplicate,
        );
    }
}
