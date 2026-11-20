<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const string DASHBOARD = '/dashboard';

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(self::DASHBOARD)->assertRedirect(route('login'));
    }

    #[Test]
    public function root_redirects_to_the_dashboard(): void
    {
        $this->get('/')->assertRedirect(self::DASHBOARD);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function portalRoles(): array
    {
        return [
            'admin' => ['admin', 'Administrador'],
            'tutor' => ['tutor', 'Tutor'],
        ];
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function portal_users_see_the_master_layout(string $role, string $roleLabel): void
    {
        $user = User::factory()->{$role}()->create();

        $this->actingAs($user)
            ->get(self::DASHBOARD)
            ->assertOk()
            ->assertSee('IVEA Analítica')
            ->assertSee('images/logo-ivea-horizontal.png', false)
            ->assertSee($user->name)
            ->assertSee($roleLabel)
            ->assertSeeInOrder(['Desempeño', 'Control de Módulos', 'Directorio de Educandos'])
            ->assertSee('Cerrar Sesión')
            ->assertSee(route('logout'), false);
    }

    #[Test]
    public function students_with_a_session_are_forbidden(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(self::DASHBOARD)
            ->assertForbidden();
    }

    #[Test]
    public function users_deactivated_mid_session_are_logged_out(): void
    {
        $user = User::factory()->tutor()->create();
        $this->actingAs($user);
        $user->forceFill(['is_active' => false])->save();

        $this->get(self::DASHBOARD)
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
