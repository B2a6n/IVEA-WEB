<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\UserRole;
use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_role_is_cast_to_enum(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTutor());
        $this->assertFalse($admin->isStudent());
    }

    #[Test]
    public function default_factory_user_is_an_active_student(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertTrue($user->is_active);
    }

    #[Test]
    public function group_belongs_to_tutor_and_has_students(): void
    {
        $tutor = User::factory()->tutor()->create();
        $group = Group::factory()->for($tutor, 'tutor')->inZone('Xalapa')->create();
        User::factory()->count(3)->student()->for($group)->create();

        $this->assertTrue($group->tutor->is($tutor));
        $this->assertCount(3, $group->students);
        $this->assertSame('Xalapa', $group->zone);
        $this->assertTrue($tutor->tutoredGroups->first()->is($group));
        $this->assertTrue($group->students->first()->group->is($group));
    }

    #[Test]
    public function role_scopes_filter_users(): void
    {
        User::factory()->admin()->create();
        User::factory()->count(2)->tutor()->create();
        User::factory()->count(4)->student()->create();

        $this->assertSame(2, User::query()->tutors()->count());
        $this->assertSame(4, User::query()->students()->count());
    }

    #[Test]
    public function progress_score_links_user_module_and_config_version(): void
    {
        $score = UserProgressScore::factory()->create();

        $this->assertInstanceOf(User::class, $score->user);
        $this->assertInstanceOf(GameModule::class, $score->gameModule);
        $this->assertInstanceOf(ConfigVersion::class, $score->configVersion);
        $this->assertTrue($score->user->progressScores->first()->is($score));
        $this->assertTrue($score->gameModule->progressScores->first()->is($score));
        $this->assertTrue($score->configVersion->progressScores->first()->is($score));
    }

    #[Test]
    public function details_json_round_trips_as_array_object(): void
    {
        $details = ['correct' => 8, 'wrong' => 2, 'attempts' => [3, 4, 1], 'level_reached' => 'intermedio'];

        $score = UserProgressScore::factory()->create(['details_json' => $details]);
        $reloaded = $score->fresh();

        $this->assertInstanceOf(ArrayObject::class, $reloaded->details_json);
        // jsonb normalizes key order, so compare by key/value rather than identity.
        $this->assertEquals($details, $reloaded->details_json->toArray());
    }

    #[Test]
    public function duplicate_client_uuid_is_rejected(): void
    {
        $existing = UserProgressScore::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        UserProgressScore::factory()->create(['client_uuid' => $existing->client_uuid]);
    }

    #[Test]
    public function deleting_user_cascades_their_scores(): void
    {
        $score = UserProgressScore::factory()->create();

        $score->user->delete();

        $this->assertModelMissing($score);
    }

    #[Test]
    public function deleting_tutor_keeps_group_without_tutor(): void
    {
        $group = Group::factory()->create();

        $group->tutor->delete();

        $this->assertNull($group->fresh()->tutor_id);
    }

    #[Test]
    public function active_scope_filters_modules(): void
    {
        GameModule::factory()->count(2)->create();
        GameModule::factory()->inactive()->create();

        $this->assertSame(2, GameModule::query()->active()->count());
    }

    #[Test]
    public function published_scope_and_current_return_latest_published_config(): void
    {
        $admin = User::factory()->admin()->create();
        ConfigVersion::factory()->for($admin, 'publisher')->create(['version' => 1]);
        $latest = ConfigVersion::factory()->for($admin, 'publisher')->create(['version' => 2]);
        ConfigVersion::factory()->draft()->create(['version' => 3]);

        $this->assertSame(2, ConfigVersion::query()->published()->count());
        $this->assertTrue(ConfigVersion::current()?->is($latest));
        $this->assertTrue($latest->publisher->is($admin));
        $this->assertIsArray($latest->payload);
    }
}
