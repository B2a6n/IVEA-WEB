<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\UserRole;
use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed inside the test transaction (not via $seed) so data never leaks into other test classes.
        $this->seed();
    }

    #[Test]
    public function seeds_one_admin_with_tutors_and_students(): void
    {
        $this->assertSame(1, User::query()->where('role', UserRole::Admin)->count());
        $this->assertSame(6, User::query()->tutors()->count());
        $this->assertGreaterThanOrEqual(100, User::query()->students()->count());
        $this->assertSame(0, User::query()->students()->whereNull('group_id')->count());
    }

    #[Test]
    public function seeds_three_groups_per_veracruz_zone(): void
    {
        $zones = Group::query()->selectRaw('zone, count(*) as total')->groupBy('zone')->pluck('total', 'zone');

        $this->assertEqualsCanonicalizing(['Xalapa', 'Veracruz', 'Córdoba'], $zones->keys()->all());
        $zones->each(fn (int $total) => $this->assertSame(3, $total));
        $this->assertSame(0, Group::query()->whereNull('tutor_id')->count());
    }

    #[Test]
    public function seeds_modules_config_versions_and_progress(): void
    {
        $this->assertGreaterThanOrEqual(8, GameModule::query()->count());
        $this->assertSame(3, ConfigVersion::query()->count());
        $this->assertNotNull(ConfigVersion::current());
        $this->assertGreaterThanOrEqual(1000, UserProgressScore::query()->count());
        $this->assertSame(
            0,
            UserProgressScore::query()->whereColumn('score', '>', 'max_score')->count(),
        );
    }
}
