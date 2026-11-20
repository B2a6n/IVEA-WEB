<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Data\EducandoDirectoryData;
use App\Models\GameModule;
use App\Models\Group;
use App\Models\User;
use App\Models\UserProgressScore;
use App\Services\EducandoDirectoryService;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * Students (alphabetical), plays relative to 2026-11-20 12:00 UTC:
 *  Ana 100% Real        Veracruz  active    no plays
 *  Guadalupe Ramos      Veracruz  active    no plays
 *  Jorge Aguilar Peña   Veracruz  inactive  sumas 50%
 *  Manuel Castillo      Xalapa    active    lectura 100%
 *  Rosaura Domínguez    Xalapa    active    letras 80%, sumas 90%, letras 60%  -> 3 plays, 2 de 3 módulos, 76.7
 * An admin and a tutor also have plays and must never be listed.
 */
final class DirectorioEducandosTest extends TestCase
{
    use RefreshDatabase;

    private const string EDUCANDOS = '/educandos';

    private const string BOM = "\xEF\xBB\xBF";

    private GameModule $letras;

    private GameModule $sumas;

    private Group $xalapa;

    private Group $veracruz;

    private User $rosaura;

    private User $admin;

    private User $tutor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-11-20 12:00:00', 'UTC'));

        $this->letras = GameModule::factory()->create(['title' => 'Reconoce las letras', 'sort_order' => 10]);
        $this->sumas = GameModule::factory()->create(['title' => 'Sumas y restas', 'sort_order' => 20]);
        $lectura = GameModule::factory()->create(['title' => 'Lectura en voz alta', 'sort_order' => 30]);
        $this->xalapa = Group::factory()->inZone('Xalapa')->create(['name' => 'Círculo Centro']);
        $this->veracruz = Group::factory()->inZone('Veracruz')->create(['name' => 'Círculo Puerto']);

        $this->rosaura = $this->student('Rosaura Domínguez Soto', 'rosaura@ivea.test', $this->xalapa);
        $manuel = $this->student('Manuel Castillo Mora', 'mcastillo@correo.test', $this->xalapa);
        $this->student('Guadalupe Ramos Viveros', 'lupita@correo.test', $this->veracruz);
        $this->student('Ana 100% Real', 'ana@correo.test', $this->veracruz);
        $jorge = $this->student('Jorge Aguilar Peña', 'jorge_ap@correo.test', $this->veracruz, isActive: false);

        $this->play($this->rosaura, $this->letras, 80, 100, '2026-11-10 10:00:00');
        $this->play($this->rosaura, $this->sumas, 9, 10, '2026-11-12 10:00:00');
        $this->play($this->rosaura, $this->letras, 60, 100, '2026-11-18 16:00:00');
        $this->play($manuel, $lectura, 100, 100, '2026-11-15 10:00:00');
        $this->play($jorge, $this->sumas, 50, 100, '2026-10-01 10:00:00');

        $this->admin = User::factory()->admin()->create(['name' => 'Administradora Central Ivea']);
        $this->tutor = User::factory()->tutor()->create(['name' => 'Tutor Pruebas Ruiz']);
        $this->play($this->admin, $this->letras, 100, 100, '2026-11-19 10:00:00');
        $this->play($this->tutor, $this->letras, 100, 100, '2026-11-19 10:00:00');
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(self::EDUCANDOS)->assertRedirect(route('login'));
    }

    #[Test]
    public function students_are_forbidden(): void
    {
        $this->actingAs($this->rosaura)->get(self::EDUCANDOS)->assertForbidden();
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
    public function portal_users_see_only_students(string $role): void
    {
        // A separate viewer: the topbar shows the signed-in user's own name.
        $response = $this->actingAs(User::factory()->{$role}()->create(['name' => 'Persona Que Consulta']))
            ->get(self::EDUCANDOS)
            ->assertOk()
            ->assertSee('Directorio de Educandos')
            ->assertDontSee('Administradora Central Ivea')
            ->assertDontSee('Tutor Pruebas Ruiz');

        // The sidebar entry, disabled until this screen existed, is now the active page.
        $this->assertMatchesRegularExpression(
            '#href="'.preg_quote(route('educandos.index'), '#').'"\s+title="Directorio de Educandos"\s+aria-current="page"#',
            (string) $response->getContent(),
        );

        $this->assertListed($response, [
            'Ana 100% Real', 'Guadalupe Ramos Viveros', 'Jorge Aguilar Peña', 'Manuel Castillo Mora',
            'Rosaura Domínguez Soto',
        ]);
    }

    #[Test]
    public function rows_show_aggregated_metrics(): void
    {
        $this->list()
            ->assertSeeInOrder([
                'Guadalupe Ramos Viveros', 'Círculo Puerto', 'Veracruz', '0 de 3 módulos', '0.0', 'Sin partidas',
                'Jorge Aguilar Peña', 'Inactivo', '50.0', 'Requiere apoyo', '01/10/2026',
                'Manuel Castillo Mora', '1 de 3 módulos', '100.0', 'Excelente', '15/11/2026',
                'Rosaura Domínguez Soto', 'Círculo Centro', 'Xalapa', '3', '2 de 3 módulos', '76.7', 'Regular',
                '18/11/2026', 'Ver Progreso',
            ]);
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function searches(): array
    {
        return [
            'by name' => ['Rosaura', ['Rosaura Domínguez Soto']],
            'case insensitive' => ['ROSA', ['Rosaura Domínguez Soto']],
            'by email' => ['correo.test', [
                'Ana 100% Real', 'Guadalupe Ramos Viveros', 'Jorge Aguilar Peña', 'Manuel Castillo Mora',
            ]],
            'percent is literal' => ['%', ['Ana 100% Real']],
            'underscore is literal' => ['_', ['Jorge Aguilar Peña']],
            'no match' => ['zzz', []],
        ];
    }

    /**
     * @param  list<string>  $expected
     */
    #[Test]
    #[DataProvider('searches')]
    public function searches_by_name_or_email(string $search, array $expected): void
    {
        $this->assertListed($this->list(['search' => $search])->assertOk(), $expected);
    }

    #[Test]
    public function filters_by_group(): void
    {
        $this->assertListed(
            $this->list(['group_id' => $this->xalapa->id]),
            ['Manuel Castillo Mora', 'Rosaura Domínguez Soto'],
        );
    }

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function statuses(): array
    {
        return [
            'active' => ['active', [
                'Ana 100% Real', 'Guadalupe Ramos Viveros', 'Manuel Castillo Mora', 'Rosaura Domínguez Soto',
            ]],
            'inactive' => ['inactive', ['Jorge Aguilar Peña']],
            'all' => ['all', [
                'Ana 100% Real', 'Guadalupe Ramos Viveros', 'Jorge Aguilar Peña', 'Manuel Castillo Mora',
                'Rosaura Domínguez Soto',
            ]],
        ];
    }

    /**
     * @param  list<string>  $expected
     */
    #[Test]
    #[DataProvider('statuses')]
    public function filters_by_status(string $status, array $expected): void
    {
        $this->assertListed($this->list(['status' => $status]), $expected);
    }

    #[Test]
    public function filters_combine(): void
    {
        $this->assertListed(
            $this->list(['search' => 'correo', 'group_id' => $this->veracruz->id, 'status' => 'active']),
            ['Ana 100% Real', 'Guadalupe Ramos Viveros'],
        );
    }

    #[Test]
    public function shows_the_empty_state_and_clear_button_when_nothing_matches(): void
    {
        $this->list()->assertDontSee('Limpiar filtros')->assertDontSee('Sin educandos que coincidan');

        $this->list(['search' => 'zzz'])
            ->assertOk()
            ->assertSee('Sin educandos que coincidan')
            ->assertSee('Limpiar filtros');
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function invalidFilters(): array
    {
        return [
            'unknown group' => [['group_id' => '999999'], 'group_id'],
            'unknown status' => [['status' => 'pending'], 'status'],
            'search too long' => [['search' => str_repeat('a', 101)], 'search'],
        ];
    }

    /**
     * @param  array<string, string>  $query
     */
    #[Test]
    #[DataProvider('invalidFilters')]
    public function rejects_invalid_filters(array $query, string $field): void
    {
        $this->list($query)
            ->assertRedirect(route('educandos.index'))
            ->assertSessionHasErrors($field);
    }

    #[Test]
    public function paginates_by_15_keeping_the_query_string(): void
    {
        User::factory()->student()->for($this->xalapa)->count(16)->create();

        $firstPage = $this->list(['group_id' => $this->xalapa->id]);
        $this->assertCount(15, $this->directory($firstPage)->students->items());
        $firstPage->assertSee('group_id='.$this->xalapa->id.'&amp;page=2', false)
            ->assertSee('Mostrando 1 a 15 de 18 educandos');

        $secondPage = $this->list(['group_id' => $this->xalapa->id, 'page' => 2]);
        $this->assertCount(3, $this->directory($secondPage)->students->items());
    }

    #[Test]
    public function the_export_button_keeps_the_active_filters(): void
    {
        $this->list(['search' => 'correo', 'status' => 'active'])
            ->assertSee(e(route('educandos.export', ['search' => 'correo', 'status' => 'active'])), false);
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function detail_returns_the_student_profile_as_json(string $role): void
    {
        $this->actingAs($this->{$role})
            ->getJson(route('educandos.show', $this->rosaura))
            ->assertOk()
            ->assertJsonStructure([
                'profile' => ['id', 'name', 'email', 'initial', 'group', 'zone', 'status'],
                'kpis' => [
                    'activities', 'average_score', 'modules_played', 'active_modules', 'last_activity', 'level',
                ],
                'recent' => [['module', 'played_at', 'score', 'max_score', 'percent']],
                'chart' => ['labels', 'average', 'plays'],
                'pdf_url',
            ])
            ->assertJsonPath('profile.name', 'Rosaura Domínguez Soto')
            ->assertJsonPath('profile.group', 'Círculo Centro')
            ->assertJsonPath('kpis.activities', 3)
            ->assertJsonPath('kpis.average_score', 76.7)
            ->assertJsonPath('kpis.modules_played', 2)
            ->assertJsonPath('kpis.active_modules', 3)
            ->assertJsonPath('kpis.level', 'Regular')
            ->assertJsonPath('recent.0.module', 'Reconoce las letras')
            ->assertJsonPath('recent.0.percent', 60)
            ->assertJsonPath('recent.1.module', 'Sumas y restas')
            ->assertJsonPath('chart', [
                'labels' => ['Reconoce las letras', 'Sumas y restas'],
                'average' => [70, 90],
                'plays' => [2, 1],
            ])
            ->assertJsonPath('pdf_url', route('educandos.pdf', $this->rosaura));
    }

    #[Test]
    public function detail_lists_at_most_the_15_latest_plays(): void
    {
        foreach (range(1, 20) as $day) {
            $this->play($this->rosaura, $this->sumas, $day, 100, sprintf('2026-09-%02d 10:00:00', $day));
        }

        $recent = $this->actingAs($this->admin)
            ->getJson(route('educandos.show', $this->rosaura))
            ->assertOk()
            ->json('recent');

        $this->assertCount(15, $recent);
        $this->assertSame('18/11/2026 10:00', $recent[0]['played_at']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function staffTargets(): array
    {
        return [
            'admin views admin' => ['admin', 'admin'],
            'admin views tutor' => ['admin', 'tutor'],
            'tutor views admin' => ['tutor', 'admin'],
            'tutor views tutor' => ['tutor', 'tutor'],
        ];
    }

    #[Test]
    #[DataProvider('staffTargets')]
    public function detail_and_pdf_are_not_found_for_staff_users(string $actor, string $target): void
    {
        $this->actingAs($this->{$actor})->getJson(route('educandos.show', $this->{$target}))->assertNotFound();
        $this->actingAs($this->{$actor})->get(route('educandos.pdf', $this->{$target}))->assertNotFound();
    }

    #[Test]
    public function detail_is_not_found_for_unknown_ids_and_requires_login(): void
    {
        $this->actingAs($this->admin)->getJson(self::EDUCANDOS.'/999999/detalle')->assertNotFound();

        auth()->logout();
        $this->get(route('educandos.show', $this->rosaura))->assertRedirect(route('login'));
    }

    #[Test]
    public function students_cannot_use_detail_export_or_pdf(): void
    {
        $this->actingAs($this->rosaura);

        $this->getJson(route('educandos.show', $this->rosaura))->assertForbidden();
        $this->get(route('educandos.export'))->assertForbidden();
        $this->get(route('educandos.pdf', $this->rosaura))->assertForbidden();
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function exports_the_filtered_directory_as_csv(string $role): void
    {
        $response = $this->actingAs($this->{$role})
            ->get(route('educandos.export', ['group_id' => $this->veracruz->id, 'status' => 'active']))
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', '10');

        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));

        $csv = $this->downloadedContent($response);

        $this->assertStringStartsWith(self::BOM, $csv);
        $this->assertStringContainsString('"Nombre","Correo","Grupo","Zona","Estado","Actividades"', $csv);
        $this->assertStringContainsString('Ana 100% Real', $csv);
        $this->assertStringContainsString('Guadalupe Ramos Viveros', $csv);
        $this->assertStringNotContainsString('Jorge Aguilar Peña', $csv);
        $this->assertStringNotContainsString('Rosaura', $csv);
        $this->assertStringNotContainsString('Tutor Pruebas Ruiz', $csv);
    }

    #[Test]
    public function each_endpoint_has_its_own_rate_limit(): void
    {
        $this->actingAs($this->admin);

        foreach (range(1, 5) as $ignored) {
            $this->getJson(route('educandos.show', $this->rosaura))->assertOk();
        }

        // Opening slide-overs must not use up the export or PDF allowance.
        $this->get(route('educandos.export'))->assertOk()->assertHeader('X-RateLimit-Remaining', '9');
        $this->get(route('educandos.pdf', $this->rosaura))
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', '20')
            ->assertHeader('X-RateLimit-Remaining', '19');
        $this->getJson(route('educandos.show', $this->rosaura))->assertHeader('X-RateLimit-Remaining', '54');
    }

    #[Test]
    public function export_query_count_does_not_grow_with_the_number_of_students(): void
    {
        $this->actingAs($this->admin);
        $baseline = $this->countQueries(fn () => $this->downloadedContent($this->get(route('educandos.export'))));

        foreach (range(1, 10) as $n) {
            $group = Group::factory()->inZone('Xalapa')->create();
            $student = $this->student("Educando Extra {$n}", "extra{$n}@correo.test", $group);
            $this->play($student, $this->letras, 50, 100, '2026-11-01 10:00:00');
        }

        $this->assertSame(
            $baseline,
            $this->countQueries(fn () => $this->downloadedContent($this->get(route('educandos.export')))),
        );
    }

    #[Test]
    public function export_includes_metrics_and_neutralizes_formulas(): void
    {
        $this->student('=HYPERLINK("http://x")', 'formula@correo.test', $this->xalapa);

        $csv = $this->downloadedContent(
            $this->actingAs($this->admin)->get(route('educandos.export', ['group_id' => $this->xalapa->id])),
        );

        $this->assertStringContainsString("\"'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('"=HYPERLINK', $csv);
        $this->assertStringContainsString(
            '"Rosaura Domínguez Soto","rosaura@ivea.test","Círculo Centro","Xalapa","Activo","3","2","76.7","18/11/2026"',
            $csv,
        );
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function downloads_the_student_record_as_pdf(string $role): void
    {
        $response = $this->actingAs($this->{$role})
            ->get(route('educandos.pdf', $this->rosaura))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('Content-Disposition'));
    }

    #[Test]
    public function the_pdf_template_has_the_institutional_header_and_history(): void
    {
        $detail = app(EducandoDirectoryService::class)->detail($this->rosaura, historyLimit: 100);
        $html = view('educandos.pdf', ['detail' => $detail])->render();

        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('Ficha Individual de Progreso', $html);
        $this->assertStringContainsString('Rosaura Domínguez Soto', $html);
        $this->assertStringContainsString('76.7', $html);
        $this->assertSame(3, substr_count($html, '<tr class="history-row">'));
    }

    private function student(string $name, string $email, Group $group, bool $isActive = true): User
    {
        return User::factory()->student()->for($group)->create([
            'name' => $name, 'email' => $email, 'is_active' => $isActive,
        ]);
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
    private function list(array $query = []): TestResponse
    {
        return $this->actingAs($this->admin)
            ->get(self::EDUCANDOS.($query === [] ? '' : '?'.http_build_query($query)));
    }

    private function directory(TestResponse $response): EducandoDirectoryData
    {
        $data = $response->viewData('directory');
        $this->assertInstanceOf(EducandoDirectoryData::class, $data);

        return $data;
    }

    /**
     * @param  list<string>  $names
     */
    private function assertListed(TestResponse $response, array $names): void
    {
        $this->assertSame($names, collect($this->directory($response)->students->items())->pluck('name')->all());
    }

    private function countQueries(Closure $callback): int
    {
        $count = 0;
        DB::listen(function () use (&$count): void {
            $count++;
        });

        $callback();

        return $count;
    }

    private function downloadedContent(TestResponse $response): string
    {
        $base = $response->baseResponse;
        $this->assertInstanceOf(BinaryFileResponse::class, $base);

        return (string) file_get_contents($base->getFile()->getPathname());
    }
}
