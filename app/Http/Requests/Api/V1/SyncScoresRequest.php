<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Data\ScoreRecordData;
use App\Data\SyncBatchData;
use App\Models\User;
use App\Models\UserProgressScore;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class SyncScoresRequest extends FormRequest
{
    public const int MAX_BATCH = 100;

    // Keep numbers well inside the PostgreSQL integer range: an overflow would abort the whole batch.
    private const int MAX_SCORE = 1_000_000;

    private const int MAX_DURATION_SECONDS = 86_400;

    private const int MAX_DETAILS_BYTES = 8_192;

    // Device clocks drift; a game played "slightly in the future" must not fail an offline batch.
    private const string PLAYED_AT_TOLERANCE = '+5 minutes';

    public function authorize(): bool
    {
        $target = User::query()->find($this->integer('user_id'));

        return $target !== null && $this->user()->can('syncFor', [UserProgressScore::class, $target]);
    }

    /**
     * Unknown game slugs are not validated here: they are rejected per record so one stale
     * module does not block the rest of an offline batch.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer'],
            'records' => ['required', 'array', 'list', 'min:1', 'max:'.self::MAX_BATCH],
            'records.*' => ['required', 'array'],
            'records.*.client_uuid' => ['required', 'uuid'],
            'records.*.game_slug' => ['required', 'string', 'max:80'],
            'records.*.score' => ['required', 'integer', 'min:0', 'lte:records.*.max_score'],
            'records.*.max_score' => ['required', 'integer', 'min:1', 'max:'.self::MAX_SCORE],
            'records.*.duration_seconds' => ['required', 'integer', 'min:0', 'max:'.self::MAX_DURATION_SECONDS],
            'records.*.played_at' => ['required', 'date', 'before_or_equal:'.self::PLAYED_AT_TOLERANCE],
            'records.*.details_json' => ['nullable', 'array', $this->maxEncodedSize(...)],
        ];
    }

    private function maxEncodedSize(string $attribute, mixed $value, Closure $fail): void
    {
        $encoded = json_encode($value);

        if ($encoded === false || strlen($encoded) > self::MAX_DETAILS_BYTES) {
            $fail('The :attribute may not be larger than '.self::MAX_DETAILS_BYTES.' bytes.');
        }
    }

    public function toDto(): SyncBatchData
    {
        /** @var list<array<string, mixed>> $records */
        $records = $this->validated('records');

        return new SyncBatchData(
            userId: (int) $this->validated('user_id'),
            records: array_map(fn (array $record) => new ScoreRecordData(
                clientUuid: strtolower($record['client_uuid']),
                gameSlug: $record['game_slug'],
                score: (int) $record['score'],
                maxScore: (int) $record['max_score'],
                durationSeconds: (int) $record['duration_seconds'],
                playedAt: CarbonImmutable::parse($record['played_at']),
                details: $record['details_json'] ?? null,
            ), $records),
        );
    }
}
