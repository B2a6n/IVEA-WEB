<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Data\DashboardData;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Seeded plays (percentage, played_at relative to 2026-11-19 12:00 UTC):
 *  ana   (Xalapa)   letras 80%  -2d    | beto  (Xalapa)   letras 60%  -2d
 *  carla (Veracruz) sumas  90%  -20d   | ana   (Xalapa)   sumas  30%  -60d
 *  carla (Veracruz) letras 40%  -120d  | tutor            letras 100% -2d (never counted)
 */
final class DashboardDesempenoTest extends TestCase
{
    use RefreshDatabase;

    private const string DASHBOARD = '/dashboard';

    private GameModule $letras;

    private GameModule $sumas;

    private Group $xalapa;

    private Group $veracruz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-19 12:00:00', 'UTC'));

        $this->letras = GameModule::factory()->create(['title' => 'Reconoce las letras', 'sort_order' => 10]);
        $this->sumas = GameModule::factory()->create(['title' => 'Sumas y restas', 'sort_order' => 20]);
        $this->xalapa = Group::factory()->inZone('Xalapa')->create(['name' => 'Círculo Centro']);
        $this->veracruz = Group::factory()->inZone('Veracruz')->create(['name' => 'Círculo Puerto']);

        $ana = User::factory()->student()->for($this->xalapa)->create();
        $beto = User::factory()->student()->for($this->xalapa)->create();
        $carla = User::factory()->student()->for($this->veracruz)->create();

        $this->play($ana, $this->letras, 80, 100, '2026-11-17 10:00:00');
        $this->play($beto, $this->letras, 60, 100, '2026-11-17 15:00:00');
        $this->play($carla, $this->sumas, 9, 10, '2026-10-30 10:00:00');
        $this->play($ana, $this->sumas, 30, 100, '2026-09-20 10:00:00');
        $this->play($carla, $this->letras, 40, 100, '2026-07-22 10:00:00');
        $this->play(User::factory()->tutor()->create(), $this->letras, 100, 100, '2026-11-17 11:00:00');
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(self::DASHBOARD)->assertRedirect(route('login'));
    }

    #[Test]
    public function students_are_forbidden(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(self::DASHBOARD)
            ->assertForbidden();
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
    public function portal_users_see_the_kpis_of_the_last_30_days(string $role): void
    {
        $response = $this->actingAs(User::factory()->{$role}()->create())
            ->get(self::DASHBOARD)
            ->assertOk()
            ->assertSeeInOrder([
                'Educandos Activos', '3',
                'Actividades Completadas', '3',
                'Promedio General', '76.7', '/ 100',
            ]);

        $this->assertKpis($response, activeStudents: 3, completedActivities: 3, averageScore: 76.7);
        $this->assertCharts(
            $response,
            trend: ['labels' => ['30/10', '17/11'], 'average' => [90.0, 70.0], 'volume' => [1, 2]],
            modules: [
                'labels' => ['Reconoce las letras', 'Sumas y restas'],
                'average' => [70.0, 90.0],
                'plays' => [2, 1],
            ],
        );
    }

    /**
     * @return array<string, array{string, int, int, float}>
     */
    public static function periods(): array
    {
        return [
            '7 days' => ['7d', 2, 2, 70.0],
            '30 days' => ['30d', 3, 3, 76.7],
            '90 days' => ['90d', 3, 4, 65.0],
            'all' => ['all', 3, 5, 60.0],
        ];
    }

    #[Test]
    #[DataProvider('periods')]
    public function filters_by_period(string $period, int $students, int $activities, float $average): void
    {
        $response = $this->dashboard(['period' => $period])->assertOk();

        $this->assertKpis($response, $students, $activities, $average);
    }

    #[Test]
    public function the_7_day_period_keeps_modules_without_plays_as_empty_bars(): void
    {
        $response = $this->dashboard(['period' => '7d']);

        $this->assertCharts(
            $response,
            trend: ['labels' => ['17/11'], 'average' => [70.0], 'volume' => [2]],
            modules: [
                'labels' => ['Reconoce las letras', 'Sumas y restas'],
                'average' => [70.0, null],
                'plays' => [2, 0],
            ],
        );
    }

    #[Test]
    public function filters_by_module(): void
    {
        $response = $this->dashboard(['module_id' => $this->letras->id])
            ->assertOk()
            ->assertSee('value="'.$this->letras->id.'" selected', false);

        $this->assertKpis($response, activeStudents: 2, completedActivities: 2, averageScore: 70.0);
        $this->assertCharts(
            $response,
            trend: ['labels' => ['17/11'], 'average' => [70.0], 'volume' => [2]],
            modules: ['labels' => ['Reconoce las letras'], 'average' => [70.0], 'plays' => [2]],
        );
    }

    #[Test]
    public function filters_by_group(): void
    {
        $response = $this->dashboard(['group_id' => $this->veracruz->id])
            ->assertOk()
            ->assertSee('Veracruz · Círculo Puerto');

        $this->assertKpis($response, activeStudents: 1, completedActivities: 1, averageScore: 90.0);
        $this->assertCharts(
            $response,
            trend: ['labels' => ['30/10'], 'average' => [90.0], 'volume' => [1]],
            modules: [
                'labels' => ['Reconoce las letras', 'Sumas y restas'],
                'average' => [null, 90.0],
                'plays' => [0, 1],
            ],
        );
    }

    #[Test]
    public function filters_combine(): void
    {
        $response = $this->dashboard([
            'module_id' => $this->letras->id, 'group_id' => $this->veracruz->id, 'period' => 'all',
        ]);

        $this->assertKpis($response, activeStudents: 1, completedActivities: 1, averageScore: 40.0);
        $this->assertCharts(
            $response,
            trend: ['labels' => ['22/07'], 'average' => [40.0], 'volume' => [1]],
            modules: ['labels' => ['Reconoce las letras'], 'average' => [40.0], 'plays' => [1]],
        );
    }

    #[Test]
    public function trend_groups_plays_by_local_date(): void
    {
        config(['ivea.reporting_timezone' => 'America/Mexico_City']);
        // 03:00 UTC on the 18th is still the evening of the 17th in Veracruz (UTC-6).
        $this->play(User::factory()->student()->create(), $this->letras, 100, 100, '2026-11-18 03:00:00');

        $response = $this->dashboard(['period' => '7d']);

        $this->assertSame(
            ['labels' => ['17/11'], 'average' => [80.0], 'volume' => [3]],
            $this->dashboardData($response)->trendChart,
        );
    }

    #[Test]
    public function period_starts_at_local_midnight(): void
    {
        config(['ivea.reporting_timezone' => 'America/Mexico_City']);
        $student = User::factory()->student()->create();
        // 7 days back starts on 2026-11-12 00:00 in Veracruz, i.e. 06:00 UTC.
        $this->play($student, $this->sumas, 50, 100, '2026-11-12 05:59:00');
        $this->play($student, $this->sumas, 70, 100, '2026-11-12 06:00:00');

        $response = $this->dashboard(['period' => '7d']);

        $this->assertKpis($response, activeStudents: 3, completedActivities: 3, averageScore: 70.0);
    }

    #[Test]
    public function rejected_filters_are_not_shown_as_selected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->followingRedirects()
            ->get(self::DASHBOARD.'?period=7d&group_id=999999')
            ->assertOk()
            ->assertSee('No se aplicaron los filtros')
            ->assertSee('value="30d" selected', false)
            ->assertDontSee('value="7d" selected', false);
    }

    #[Test]
    public function clear_filters_button_is_shown_only_when_filtering(): void
    {
        $this->dashboard()->assertDontSee('Limpiar filtros');
        $this->dashboard(['period' => '30d'])->assertDontSee('Limpiar filtros');
        $this->dashboard(['period' => '7d'])->assertSee('Limpiar filtros');
        $this->dashboard(['group_id' => $this->xalapa->id])->assertSee('Limpiar filtros');
    }

    #[Test]
    public function empty_database_shows_the_empty_state_without_errors(): void
    {
        UserProgressScore::query()->delete();

        $response = $this->dashboard(['period' => 'all'])
            ->assertOk()
            ->assertSee('Sin partidas registradas')
            ->assertSee('0.0')
            ->assertDontSee('<canvas', false);

        $this->assertKpis($response, activeStudents: 0, completedActivities: 0, averageScore: 0.0);
    }

    #[Test]
    public function filter_without_results_shows_the_empty_state(): void
    {
        $response = $this->dashboard(['module_id' => $this->sumas->id, 'period' => '7d'])
            ->assertOk()
            ->assertSee('Sin partidas registradas')
            ->assertSee('Limpiar filtros')
            ->assertDontSee('<canvas', false);

        $this->assertKpis($response, activeStudents: 0, completedActivities: 0, averageScore: 0.0);
    }

    #[Test]
    public function charts_are_rendered_when_there_are_plays(): void
    {
        $this->dashboard()
            ->assertSee('Tendencia de Desempeño')
            ->assertSee('Rendimiento por Módulo')
            ->assertSee('<canvas', false)
            ->assertDontSee('Sin partidas registradas');
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function invalidFilters(): array
    {
        return [
            'unknown module' => [['module_id' => '999999'], 'module_id'],
            'non numeric module' => [['module_id' => 'abc'], 'module_id'],
            'unknown group' => [['group_id' => '999999'], 'group_id'],
            'unknown period' => [['period' => '1y'], 'period'],
        ];
    }

    /**
     * @param  array<string, string>  $query
     */
    #[Test]
    #[DataProvider('invalidFilters')]
    public function rejects_invalid_filters(array $query, string $field): void
    {
        $this->dashboard($query)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors($field);
    }

    private function play(User $user, GameModule $module, int $score, int $maxScore, string $playedAt): void
    {
        UserProgressScore::factory()->for($user)->create([
            'games_module_id' => $module->id,
            'score' => $score,
            'max_score' => $maxScore,
            'played_at' => Carbon::parse($playedAt, 'UTC'),
        ]);
    }

    /**
     * @param  array<string, int|string>  $query
     */
    private function dashboard(array $query = []): TestResponse
    {
        return $this->actingAs(User::factory()->admin()->create())
            ->get(self::DASHBOARD.($query === [] ? '' : '?'.http_build_query($query)));
    }

    private function dashboardData(TestResponse $response): DashboardData
    {
        $data = $response->viewData('dashboard');
        $this->assertInstanceOf(DashboardData::class, $data);

        return $data;
    }

    private function assertKpis(
        TestResponse $response,
        int $activeStudents,
        int $completedActivities,
        float $averageScore,
    ): void {
        $data = $this->dashboardData($response);

        $this->assertSame($activeStudents, $data->activeStudents);
        $this->assertSame($completedActivities, $data->completedActivities);
        $this->assertSame($averageScore, $data->averageScore);
    }

    /**
     * Checks both the view data and the JSON the page hands to Chart.js.
     *
     * @param  array<string, list<mixed>>  $trend
     * @param  array<string, list<mixed>>  $modules
     */
    private function assertCharts(TestResponse $response, array $trend, array $modules): void
    {
        $data = $this->dashboardData($response);

        $this->assertSame($trend, $data->trendChart);
        $this->assertSame($modules, $data->moduleChart);

        $response->assertSee('data-chart="'.e(json_encode($trend, JSON_THROW_ON_ERROR)).'"', false);
        $response->assertSee('data-chart="'.e(json_encode($modules, JSON_THROW_ON_ERROR)).'"', false);
    }
}
