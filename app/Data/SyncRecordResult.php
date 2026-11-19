<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\SyncStatus;

final readonly class SyncRecordResult
{
    public const string REASON_UNKNOWN_GAME_SLUG = 'unknown_game_slug';

    public const string REASON_UUID_CONFLICT = 'uuid_conflict';

    public function __construct(
        public string $clientUuid,
        public SyncStatus $status,
        public ?string $reason = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $result = ['client_uuid' => $this->clientUuid, 'status' => $this->status->value];

        if ($this->reason !== null) {
            $result['reason'] = $this->reason;
        }

        return $result;
    }
}
