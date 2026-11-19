<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SyncBatchData
{
    /**
     * @param  list<ScoreRecordData>  $records
     */
    public function __construct(
        public int $userId,
        public array $records,
    ) {}
}
