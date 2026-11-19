<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MobileSyncTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/v1/mobile/sync';

    private const int MAX_BATCH = 100;

    private GameModule $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->module = GameModule::factory()->create(['slug' => 'sumas-basicas']);
    }

    #[Test]
    public function requires_authentication(): void
    {
        $student = User::factory()->student()->create();

        $this->postJson(self::ENDPOINT, $this->payload($student, [$this->record()]))
            ->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    #[Test]
    public function stores_new_records_resolving_module_from_slug(): void
    {
        $student = User::factory()->student()->create();
        $record = $this->record([
            'score' => 70,
            'max_score' => 100,
            'duration_seconds' => 312,
            'played_at' => '2026-11-10T15:30:00+00:00',
            'details_json' => ['correct' => 7, 'wrong' => 3, 'device' => 'android'],
        ]);
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [$record]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.received', 1)
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.duplicates', 0)
            ->assertJsonPath('data.rejected', 0)
            ->assertJsonPath('data.results.0', ['client_uuid' => $record['client_uuid'], 'status' => 'created']);

        $score = UserProgressScore::query()->where('client_uuid', $record['client_uuid'])->sole();
        $this->assertSame($student->id, $score->user_id);
        $this->assertSame($this->module->id, $score->games_module_id);
        $this->assertNull($score->config_version_id);
        $this->assertSame(70, $score->score);
        $this->assertSame(100, $score->max_score);
        $this->assertSame(312, $score->duration_seconds);
        $this->assertTrue($score->played_at->equalTo('2026-11-10T15:30:00+00:00'));
        // jsonb normalizes key order, so compare content rather than order.
        $this->assertEquals(
            ['correct' => 7, 'wrong' => 3, 'device' => 'android'],
            $score->details_json->getArrayCopy(),
        );
    }

    #[Test]
    public function resending_same_batch_reports_all_records_as_duplicates(): void
    {
        $student = User::factory()->student()->create();
        $payload = $this->payload($student, [$this->record(), $this->record()]);
        Sanctum::actingAs($student);
        $this->postJson(self::ENDPOINT, $payload)->assertOk();

        $this->postJson(self::ENDPOINT, $payload)
            ->assertOk()
            ->assertJsonPath('data.received', 2)
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.duplicates', 2)
            ->assertJsonPath('data.results.*.status', ['duplicate', 'duplicate']);

        $this->assertDatabaseCount('user_progress_scores', 2);
    }

    #[Test]
    public function mixed_batch_reports_created_and_duplicates_separately(): void
    {
        $student = User::factory()->student()->create();
        $existing = UserProgressScore::factory()->for($student)->for($this->module)->create();
        $new = $this->record();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [
            $this->record(['client_uuid' => $existing->client_uuid]),
            $new,
        ]))
            ->assertOk()
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.duplicates', 1)
            ->assertJsonPath('data.results', [
                ['client_uuid' => $existing->client_uuid, 'status' => 'duplicate'],
                ['client_uuid' => $new['client_uuid'], 'status' => 'created'],
            ]);

        $this->assertDatabaseCount('user_progress_scores', 2);
    }

    #[Test]
    public function duplicate_client_uuid_inside_same_batch_is_stored_once(): void
    {
        $student = User::factory()->student()->create();
        $record = $this->record();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [$record, $record]))
            ->assertOk()
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.duplicates', 1)
            ->assertJsonPath('data.results.*.status', ['created', 'duplicate']);

        $this->assertDatabaseCount('user_progress_scores', 1);
    }

    #[Test]
    public function uuid_owned_by_another_user_is_rejected_and_not_overwritten(): void
    {
        $owner = User::factory()->student()->create();
        $existing = UserProgressScore::factory()->for($owner)->for($this->module)->create(['score' => 10]);
        $student = User::factory()->student()->create();
        $new = $this->record();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [
            $this->record(['client_uuid' => $existing->client_uuid, 'score' => 90]),
            $new,
        ]))
            ->assertOk()
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.duplicates', 0)
            ->assertJsonPath('data.rejected', 1)
            ->assertJsonPath('data.results', [
                ['client_uuid' => $existing->client_uuid, 'status' => 'rejected', 'reason' => 'uuid_conflict'],
                ['client_uuid' => $new['client_uuid'], 'status' => 'created'],
            ]);

        $fresh = $existing->fresh();
        $this->assertSame($owner->id, $fresh->user_id);
        $this->assertSame(10, $fresh->score);
    }

    #[Test]
    public function unknown_slug_is_rejected_while_valid_records_are_stored(): void
    {
        $student = User::factory()->student()->create();
        $unknown = $this->record(['game_slug' => 'juego-inexistente']);
        $valid = $this->record();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [$unknown, $valid]))
            ->assertOk()
            ->assertJsonPath('data.received', 2)
            ->assertJsonPath('data.created', 1)
            ->assertJsonPath('data.duplicates', 0)
            ->assertJsonPath('data.rejected', 1)
            ->assertJsonPath('data.results', [
                ['client_uuid' => $unknown['client_uuid'], 'status' => 'rejected', 'reason' => 'unknown_game_slug'],
                ['client_uuid' => $valid['client_uuid'], 'status' => 'created'],
            ]);

        $this->assertDatabaseMissing('user_progress_scores', ['client_uuid' => $unknown['client_uuid']]);
        $this->assertDatabaseHas('user_progress_scores', ['client_uuid' => $valid['client_uuid']]);
    }

    #[Test]
    public function accepts_scores_for_inactive_modules(): void
    {
        // The game may have been played offline before the module was deactivated.
        GameModule::factory()->inactive()->create(['slug' => 'modulo-retirado']);
        $student = User::factory()->student()->create();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [
            $this->record(['game_slug' => 'modulo-retirado']),
        ]))
            ->assertOk()
            ->assertJsonPath('data.created', 1);
    }

    #[Test]
    public function accepts_played_at_within_device_clock_skew_tolerance(): void
    {
        $student = User::factory()->student()->create();
        Sanctum::actingAs($student);

        $this->postJson(self::ENDPOINT, $this->payload($student, [
            $this->record(['played_at' => now()->addMinutes(2)->toIso8601String()]),
        ]))
            ->assertOk()
            ->assertJsonPath('data.created', 1);
    }

    #[Test]
    public function inactive_user_cannot_sync_own_scores(): void
    {
        $student = User::factory()->student()->create(['is_active' => false]);
        Sanctum::actingAs($student);

        $this->assertForbiddenSync($student);
    }

    #[Test]
    public function tutor_cannot_sync_for_an_inactive_student(): void
    {
        $tutor = User::factory()->tutor()->create();
        $group = Group::factory()->for($tutor, 'tutor')->create();
        $student = User::factory()->student()->for($group)->create(['is_active' => false]);
        Sanctum::actingAs($tutor);

        $this->assertForbiddenSync($student);
    }

    #[Test]
    public function tutor_can_sync_for_a_student_of_a_tutored_group(): void
    {
        $tutor = User::factory()->tutor()->create();
        $group = Group::factory()->for($tutor, 'tutor')->create();
        $student = User::factory()->student()->for($group)->create();
        Sanctum::actingAs($tutor);

        $this->postJson(self::ENDPOINT, $this->payload($student, [$this->record()]))
            ->assertOk()
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('user_progress_scores', ['user_id' => $student->id]);
    }

    #[Test]
    public function tutor_cannot_sync_for_a_student_outside_tutored_groups(): void
    {
        $tutor = User::factory()->tutor()->create();
        Group::factory()->for($tutor, 'tutor')->create();
        $otherGroup = Group::factory()->create();
        $student = User::factory()->student()->for($otherGroup)->create();
        Sanctum::actingAs($tutor);

        $this->assertForbiddenSync($student);
    }

    #[Test]
    public function tutor_cannot_sync_for_a_non_student_member_of_tutored_group(): void
    {
        $tutor = User::factory()->tutor()->create();
        $group = Group::factory()->for($tutor, 'tutor')->create();
        $otherTutor = User::factory()->tutor()->for($group)->create();
        Sanctum::actingAs($tutor);

        $this->assertForbiddenSync($otherTutor);
    }

    #[Test]
    public function student_cannot_sync_for_another_student(): void
    {
        $group = Group::factory()->create();
        $classmate = User::factory()->student()->for($group)->create();
        Sanctum::actingAs(User::factory()->student()->for($group)->create());

        $this->assertForbiddenSync($classmate);
    }

    #[Test]
    public function admin_cannot_sync_on_behalf_of_a_student(): void
    {
        $student = User::factory()->student()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->assertForbiddenSync($student);
    }

    #[Test]
    public function nonexistent_target_user_is_forbidden(): void
    {
        Sanctum::actingAs(User::factory()->student()->create());

        $this->postJson(self::ENDPOINT, ['user_id' => 999999, 'records' => [$this->record()]])
            ->assertForbidden()
            ->assertJsonPath('error.code', 'forbidden');
    }

    /**
     * @param  Closure(array<string, mixed>): array<string, mixed>  $mutate
     */
    #[Test]
    #[DataProvider('invalidPayloads')]
    public function rejects_invalid_payload(Closure $mutate, string $field): void
    {
        $student = User::factory()->student()->create();
        Sanctum::actingAs($student);

        $response = $this->postJson(self::ENDPOINT, $mutate($this->payload($student, [$this->record()])))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'validation_error');

        $this->assertArrayHasKey($field, $response->json('error.details'));
        $this->assertDatabaseCount('user_progress_scores', 0);
    }

    /**
     * @return array<string, array{Closure(array<string, mixed>): array<string, mixed>, string}>
     */
    public static function invalidPayloads(): array
    {
        $setField = fn (string $key, mixed $value) => function (array $payload) use ($key, $value): array {
            $payload['records'][0][$key] = $value;

            return $payload;
        };
        $dropField = fn (string $key) => function (array $payload) use ($key): array {
            unset($payload['records'][0][$key]);

            return $payload;
        };

        return [
            'missing records' => [fn (array $p) => ['user_id' => $p['user_id']], 'records'],
            'records not an array' => [fn (array $p) => [...$p, 'records' => 'x'], 'records'],
            'empty records' => [fn (array $p) => [...$p, 'records' => []], 'records'],
            'batch too large' => [
                fn (array $p) => [...$p, 'records' => array_fill(0, self::MAX_BATCH + 1, $p['records'][0])],
                'records',
            ],
            'missing client_uuid' => [$dropField('client_uuid'), 'records.0.client_uuid'],
            'invalid client_uuid' => [$setField('client_uuid', 'not-a-uuid'), 'records.0.client_uuid'],
            'missing game_slug' => [$dropField('game_slug'), 'records.0.game_slug'],
            'missing score' => [$dropField('score'), 'records.0.score'],
            'negative score' => [$setField('score', -1), 'records.0.score'],
            'score above max_score' => [$setField('score', 101), 'records.0.score'],
            'zero max_score' => [$setField('max_score', 0), 'records.0.max_score'],
            'negative duration' => [$setField('duration_seconds', -5), 'records.0.duration_seconds'],
            'invalid played_at' => [$setField('played_at', 'not-a-date'), 'records.0.played_at'],
            'future played_at' => [$setField('played_at', '2999-01-01T00:00:00+00:00'), 'records.0.played_at'],
            'details_json not an object' => [$setField('details_json', 'texto'), 'records.0.details_json'],
            'details_json too large' => [
                $setField('details_json', ['notes' => str_repeat('a', 9000)]),
                'records.0.details_json',
            ],
            // Beyond the PostgreSQL integer range: must be a 422, never a 500 that drops the batch.
            'max_score out of range' => [$setField('max_score', 3_000_000_000), 'records.0.max_score'],
            'duration out of range' => [$setField('duration_seconds', 3_000_000_000), 'records.0.duration_seconds'],
        ];
    }

    private function assertForbiddenSync(User $target): void
    {
        $this->postJson(self::ENDPOINT, $this->payload($target, [$this->record()]))
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('data', null)
            ->assertJsonPath('error.code', 'forbidden');

        $this->assertDatabaseCount('user_progress_scores', 0);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return array<string, mixed>
     */
    private function payload(User $target, array $records): array
    {
        return ['user_id' => $target->id, 'records' => $records];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function record(array $overrides = []): array
    {
        return [
            'client_uuid' => (string) Str::uuid(),
            'game_slug' => 'sumas-basicas',
            'score' => 80,
            'max_score' => 100,
            'duration_seconds' => 240,
            'played_at' => now()->subHour()->toIso8601String(),
            'details_json' => ['correct' => 8, 'wrong' => 2],
            ...$overrides,
        ];
    }
}
