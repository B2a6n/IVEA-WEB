<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MobileConfigTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/v1/mobile/config';

    #[Test]
    public function requires_authentication(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertUnauthorized()
            ->assertExactJson([
                'success' => false,
                'data' => null,
                'error' => ['code' => 'unauthenticated', 'message' => 'Unauthenticated.'],
                'meta' => null,
            ]);
    }

    #[Test]
    public function accepts_a_sanctum_bearer_token(): void
    {
        $token = User::factory()->student()->create()->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson(self::ENDPOINT)->assertOk();
    }

    #[Test]
    public function is_rate_limited(): void
    {
        Sanctum::actingAs(User::factory()->student()->create());

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit');
    }

    #[Test]
    public function unknown_api_route_returns_not_found_envelope(): void
    {
        $this->getJson('/api/v1/mobile/inexistente')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'not_found');
    }

    #[Test]
    public function returns_latest_published_config_version(): void
    {
        ConfigVersion::factory()->create(['version' => 1, 'published_at' => now()->subDays(10)]);
        $latest = ConfigVersion::factory()->create(['version' => 3, 'published_at' => now()->subDay()]);
        ConfigVersion::factory()->draft()->create(['version' => 4]);
        Sanctum::actingAs(User::factory()->student()->create());

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('error', null)
            ->assertJsonPath('data.config_version.version', 3)
            ->assertJsonPath('data.config_version.published_at', $latest->published_at->toIso8601String());
    }

    #[Test]
    public function ignores_versions_scheduled_in_the_future(): void
    {
        ConfigVersion::factory()->create(['version' => 2, 'published_at' => now()->subDay()]);
        ConfigVersion::factory()->create(['version' => 5, 'published_at' => now()->addDay()]);
        Sanctum::actingAs(User::factory()->student()->create());

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('data.config_version.version', 2);
    }

    #[Test]
    public function exposes_only_version_and_published_at_of_the_config(): void
    {
        ConfigVersion::factory()->create();
        Sanctum::actingAs(User::factory()->student()->create());

        $config = $this->getJson(self::ENDPOINT)->assertOk()->json('data.config_version');

        $this->assertSame(['version', 'published_at'], array_keys($config));
    }

    #[Test]
    public function returns_null_config_version_when_none_is_published(): void
    {
        ConfigVersion::factory()->draft()->create();
        GameModule::factory()->create();
        Sanctum::actingAs(User::factory()->student()->create());

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJsonPath('data.config_version', null)
            ->assertJsonCount(1, 'data.modules');
    }

    #[Test]
    public function returns_only_active_modules_ordered_by_sort_order(): void
    {
        GameModule::factory()->create(['slug' => 'tercero', 'sort_order' => 30]);
        GameModule::factory()->create(['slug' => 'primero', 'sort_order' => 10]);
        GameModule::factory()->inactive()->create(['slug' => 'apagado', 'sort_order' => 5]);
        GameModule::factory()->create(['slug' => 'segundo', 'sort_order' => 20]);
        Sanctum::actingAs(User::factory()->student()->create());

        $slugs = $this->getJson(self::ENDPOINT)->assertOk()->json('data.modules.*.slug');

        $this->assertSame(['primero', 'segundo', 'tercero'], $slugs);
    }

    #[Test]
    public function exposes_only_public_module_fields(): void
    {
        $module = GameModule::factory()->create([
            'slug' => 'sumas-basicas',
            'title' => 'Sumas básicas',
            'description' => 'Suma números de una cifra.',
            'subject_area' => 'matematicas',
            'difficulty_level' => 2,
        ]);
        Sanctum::actingAs(User::factory()->student()->create());

        $this->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertExactJson([
                'success' => true,
                'data' => [
                    'config_version' => null,
                    'modules' => [[
                        'slug' => $module->slug,
                        'title' => $module->title,
                        'description' => $module->description,
                        'subject_area' => $module->subject_area,
                        'difficulty_level' => 2,
                    ]],
                ],
                'error' => null,
                'meta' => null,
            ]);
    }
}
