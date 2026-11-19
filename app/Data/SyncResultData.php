<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SyncStatus;

/**
 * Per-record outcome of a sync batch, in the same order the records were received.
 */
final readonly class SyncResultData
{
    /**
     * @param  list<SyncRecordResult>  $results
     */
    public function __construct(
        public array $results,
    ) {}

    public function count(SyncStatus $status): int
    {
        return count(array_filter($this->results, fn (SyncRecordResult $result) => $result->status === $status));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'received' => count($this->results),
            'created' => $this->count(SyncStatus::Created),
            'duplicates' => $this->count(SyncStatus::Duplicate),
            'rejected' => $this->count(SyncStatus::Rejected),
            'results' => array_map(fn (SyncRecordResult $result) => $result->toArray(), $this->results),
        ];
    }
}
