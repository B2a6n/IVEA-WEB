<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\ConfigVersion;
use App\Models\GameModule;
use App\Models\User;
use App\Models\UserProgressScore;
use App\Services\MobileConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ControlModulosTest extends TestCase
{
    use RefreshDatabase;

    private const string MODULOS = '/modulos';

    private const array BASE_PAYLOAD = ['min_app_version' => '1.1.0', 'sync_interval_minutes' => 30];

    private User $admin;

    private GameModule $letras;

    private GameModule $sumas;

    private ConfigVersion $currentVersion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->sumas = GameModule::factory()->create([
            'slug' => 'sumas-y-restas', 'title' => 'Sumas y restas', 'sort_order' => 20, 'is_active' => true,
        ]);
        $this->letras = GameModule::factory()->create([
            'slug' => 'reconoce-letras', 'title' => 'Reconoce las letras', 'sort_order' => 10, 'is_active' => true,
        ]);
        ConfigVersion::factory()->create([
            'version' => 1, 'payload' => ['min_app_version' => '1.0.0'], 'published_at' => now()->subDays(10),
        ]);
        $this->currentVersion = ConfigVersion::factory()->create([
            'version' => 2, 'payload' => self::BASE_PAYLOAD, 'published_at' => now()->subDay(),
        ]);
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(self::MODULOS)->assertRedirect(route('login'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function portalRoles(): array
    {
        return ['admin' => ['admin'], 'tutor' => ['tutor']];
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function portal_users_see_modules_in_sort_order_with_play_counts(string $role): void
    {
        $scores = UserProgressScore::factory()->for($this->currentVersion);
        $scores->count(3)->create(['games_module_id' => $this->letras->id]);
        $scores->create(['games_module_id' => $this->sumas->id]);

        $this->actingAs(User::factory()->{$role}()->create())
            ->get(self::MODULOS)
            ->assertOk()
            ->assertSee('Control de Módulos')
            ->assertSee('Versión activa: v2')
            ->assertSeeInOrder([
                'Reconoce las letras', 'reconoce-letras', '3 partidas jugadas',
                'Sumas y restas', 'sumas-y-restas', '1 partida jugada',
            ]);
    }

    #[Test]
    public function students_are_forbidden(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(self::MODULOS)
            ->assertForbidden();
    }

    #[Test]
    public function admin_sees_the_save_button_and_editable_toggles(): void
    {
        $this->actingAs($this->admin)
            ->get(self::MODULOS)
            ->assertOk()
            ->assertSee('Guardar Cambios')
            ->assertSee('id="modulos-form"', false)
            ->assertSee('name="base_version" value="2"', false)
            ->assertSee('name="modules['.$this->letras->id.']"', false)
            ->assertDontSee('disabled="disabled"', false);
    }

    #[Test]
    public function tutor_sees_a_read_only_view(): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->get(self::MODULOS)
            ->assertOk()
            ->assertDontSee('Guardar Cambios')
            ->assertDontSee('id="modulos-form"', false)
            ->assertDontSee('name="modules[', false)
            ->assertSee('role="switch"', false)
            ->assertSee('disabled="disabled"', false)
            ->assertSee('Tu rol de tutor es de solo lectura.');
    }

    #[Test]
    public function admin_can_toggle_modules_and_publish_a_new_version(): void
    {
        $this->sumas->forceFill(['is_active' => false])->save();

        $this->save([$this->letras->id => '0', $this->sumas->id => '1'])
            ->assertRedirect(route('modulos.index'))
            ->assertSessionHas('status');

        $this->assertFalse($this->letras->refresh()->is_active);
        $this->assertTrue($this->sumas->refresh()->is_active);

        $version = ConfigVersion::query()->where('version', 3)->firstOrFail();
        $this->assertSame($this->admin->id, $version->published_by);
        $this->assertNotNull($version->published_at);
        // jsonb does not keep key order, so compare as maps.
        $this->assertEquals(
            [...self::BASE_PAYLOAD, 'modules' => ['reconoce-letras' => false, 'sumas-y-restas' => true]],
            $version->payload,
        );
        $this->assertFalse($version->payload['modules']['reconoce-letras']);
        $this->assertTrue($version->payload['modules']['sumas-y-restas']);

        $this->get(self::MODULOS)->assertSee('Versión activa: v3');
    }

    #[Test]
    public function the_confirmation_alert_is_shown_after_saving(): void
    {
        $this->followingRedirects()
            ->save([$this->letras->id => '0', $this->sumas->id => '1'])
            ->assertOk()
            ->assertSee('Cambios guardados. Versión activa: v3.');
    }

    #[Test]
    public function mobile_config_reflects_the_change_immediately(): void
    {
        $this->save([$this->letras->id => '0', $this->sumas->id => '1']);

        Sanctum::actingAs(User::factory()->student()->create());

        $response = $this->getJson('/api/v1/mobile/config')->assertOk();

        $response->assertJsonPath('data.config_version.version', 3);
        $this->assertSame(['sumas-y-restas'], $response->json('data.modules.*.slug'));
    }

    #[Test]
    public function partial_payload_leaves_other_modules_untouched_but_snapshots_all(): void
    {
        $this->save([$this->letras->id => '0'])->assertSessionHas('status');

        $this->assertTrue($this->sumas->refresh()->is_active);
        $this->assertEquals(
            ['reconoce-letras' => false, 'sumas-y-restas' => true],
            ConfigVersion::query()->where('version', 3)->firstOrFail()->payload['modules'],
        );
    }

    #[Test]
    public function new_version_is_numbered_after_existing_drafts(): void
    {
        ConfigVersion::factory()->draft()->create(['version' => 3]);

        $this->save([$this->letras->id => '0'], baseVersion: 3);

        $this->assertDatabaseHas('config_versions', ['version' => 4, 'published_by' => $this->admin->id]);
    }

    #[Test]
    public function first_publication_starts_at_version_one(): void
    {
        ConfigVersion::query()->delete();

        $this->save([$this->letras->id => '0'], baseVersion: null)->assertSessionHas('status');

        $version = ConfigVersion::query()->sole();
        $this->assertSame(1, $version->version);
        $this->assertEquals(['modules' => ['reconoce-letras' => false, 'sumas-y-restas' => true]], $version->payload);
    }

    #[Test]
    public function saving_without_changes_does_not_create_a_version(): void
    {
        $this->followingRedirects()
            ->save([$this->letras->id => '1', $this->sumas->id => '1'])
            ->assertOk()
            ->assertSee('No hubo cambios que guardar.');

        $this->assertDatabaseCount('config_versions', 2);
    }

    #[Test]
    public function rejects_a_save_based_on_an_outdated_version(): void
    {
        // Another admin published v3 after this form was rendered with base_version 2.
        ConfigVersion::factory()->create(['version' => 3, 'payload' => self::BASE_PAYLOAD]);

        $this->from(self::MODULOS)
            ->save([$this->letras->id => '0'])
            ->assertRedirect(route('modulos.index'))
            ->assertSessionHasErrors('base_version');

        $this->assertTrue($this->letras->refresh()->is_active);
        $this->assertDatabaseCount('config_versions', 3);

        $this->followingRedirects()
            ->save([$this->letras->id => '0'])
            ->assertSee('Otro administrador publicó la versión v3');
    }

    #[Test]
    public function tutor_cannot_update_modules(): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->put(self::MODULOS, ['modules' => [$this->letras->id => '0'], 'base_version' => 2])
            ->assertForbidden();

        $this->assertTrue($this->letras->refresh()->is_active);
        $this->assertDatabaseCount('config_versions', 2);
    }

    /**
     * Module key `letras` is replaced by the id created in setUp.
     *
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'missing modules' => [['base_version' => 2]],
            'modules is not an array' => [['modules' => 'todos', 'base_version' => 2]],
            'non boolean value' => [['modules' => ['letras' => 'quizas'], 'base_version' => 2]],
            'unknown module id' => [['modules' => [999999 => '0'], 'base_version' => 2]],
            'non integer key' => [['modules' => ['abc' => '0'], 'base_version' => 2]],
            'known and unknown ids' => [['modules' => ['letras' => '0', 999999 => '0'], 'base_version' => 2]],
            'too many modules' => [['modules' => array_fill(100000, 201, '1'), 'base_version' => 2]],
            'missing base version' => [['modules' => ['letras' => '0']]],
            'non integer base version' => [['modules' => ['letras' => '0'], 'base_version' => 'v2']],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[Test]
    #[DataProvider('invalidPayloads')]
    public function rejects_invalid_payloads(array $payload): void
    {
        if (is_array($payload['modules'] ?? null)) {
            $payload['modules'] = collect($payload['modules'])
                ->mapWithKeys(fn (string $state, int|string $key) => [
                    $key === 'letras' ? $this->letras->id : $key => $state,
                ])
                ->all();
        }

        $this->actingAs($this->admin)
            ->from(self::MODULOS)
            ->put(self::MODULOS, $payload)
            ->assertRedirect(self::MODULOS)
            ->assertSessionHasErrors();

        $this->assertTrue($this->letras->refresh()->is_active);
        $this->assertTrue($this->sumas->refresh()->is_active);
        $this->assertDatabaseCount('config_versions', 2);
    }

    #[Test]
    public function toggles_keep_the_submitted_state_after_a_validation_error(): void
    {
        $this->from(self::MODULOS)
            ->followingRedirects()
            ->save([$this->letras->id => '0', 999999 => '0'])
            ->assertOk()
            ->assertSee('No se guardaron los cambios: Uno o más módulos no existen.')
            ->assertSee('>Deshabilitado</span>', false);
    }

    #[Test]
    public function the_save_endpoint_is_throttled(): void
    {
        $this->save([$this->letras->id => '1'])->assertHeader('X-RateLimit-Limit', '30');
    }

    #[Test]
    public function invalidates_the_mobile_config_cache(): void
    {
        Cache::put(MobileConfigService::CACHE_KEY, 'stale', 3600);

        $this->save([$this->letras->id => '0']);

        $this->assertTrue(Cache::missing(MobileConfigService::CACHE_KEY));
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function save(array $modules, ?int $baseVersion = 2): TestResponse
    {
        return $this->actingAs($this->admin)
            ->put(self::MODULOS, ['modules' => $modules, 'base_version' => $baseVersion]);
    }
}
