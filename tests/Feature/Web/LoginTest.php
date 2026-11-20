<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const string LOGIN = '/login';

    private const string PASSWORD = 'password';

    private const string INVALID_CREDENTIALS = 'Las credenciales proporcionadas no coinciden con nuestros registros.';

    #[Test]
    public function renders_the_login_page(): void
    {
        $this->get(self::LOGIN)
            ->assertOk()
            ->assertSee('Acceso al Portal')
            ->assertSee('images/logo-ivea-horizontal.png', false)
            ->assertSee('Recordar sesión')
            ->assertSee(route('password.request'), false);
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
    public function active_portal_users_can_log_in(string $role): void
    {
        $user = User::factory()->{$role}()->create();

        $this->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_updates_last_login_at(): void
    {
        $user = User::factory()->admin()->create(['last_login_at' => null]);
        $this->freezeSecond();

        $this->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD]);

        $this->assertSame(now()->toDateTimeString(), $user->refresh()->last_login_at?->toDateTimeString());
    }

    #[Test]
    public function remember_me_sets_the_recaller_cookie(): void
    {
        $user = User::factory()->tutor()->create();

        $this->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD, 'remember' => '1'])
            ->assertCookie(Auth::guard('web')->getRecallerName());
    }

    #[Test]
    public function login_without_remember_does_not_set_the_recaller_cookie(): void
    {
        $user = User::factory()->tutor()->create();

        $this->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertCookieMissing(Auth::guard('web')->getRecallerName());
    }

    #[Test]
    public function wrong_password_is_rejected_with_a_generic_message(): void
    {
        $user = User::factory()->admin()->create();

        $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertRedirect(self::LOGIN)
            ->assertSessionHasErrors(['email' => self::INVALID_CREDENTIALS])
            ->assertSessionDoesntHaveErrors('password');

        $this->assertGuest();
    }

    #[Test]
    public function unknown_email_gets_the_same_generic_message(): void
    {
        $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => 'nadie@ivea.gob.mx', 'password' => self::PASSWORD])
            ->assertSessionHasErrors(['email' => self::INVALID_CREDENTIALS]);

        $this->assertGuest();
    }

    #[Test]
    public function students_cannot_use_the_web_portal(): void
    {
        $student = User::factory()->student()->create();

        $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => $student->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors([
                'email' => 'El portal web es exclusivo para administradores y tutores. '
                    .'Los educandos deben usar la aplicación móvil IVEA.',
            ]);

        $this->assertGuest();
    }

    #[Test]
    #[DataProvider('portalRoles')]
    public function inactive_users_cannot_log_in(string $role): void
    {
        $user = User::factory()->{$role}()->inactive()->create();

        $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors([
                'email' => 'Tu cuenta está desactivada. Contacta a la Mesa Técnica IVEA.',
            ]);

        $this->assertGuest();
    }

    #[Test]
    public function rejected_login_does_not_touch_last_login_at(): void
    {
        $student = User::factory()->student()->create(['last_login_at' => null]);

        $this->post(self::LOGIN, ['email' => $student->email, 'password' => self::PASSWORD]);

        $this->assertNull($student->refresh()->last_login_at);
    }

    #[Test]
    public function email_and_password_are_required_with_spanish_messages(): void
    {
        $this->from(self::LOGIN)
            ->post(self::LOGIN, [])
            ->assertSessionHasErrors([
                'email' => 'El campo correo electrónico es obligatorio.',
                'password' => 'El campo contraseña es obligatorio.',
            ]);
    }

    #[Test]
    public function login_is_throttled_after_five_failed_attempts(): void
    {
        $user = User::factory()->admin()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(self::LOGIN, ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $response = $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertSessionHasErrors('email');
        $this->assertStringStartsWith('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }

    #[Test]
    public function password_spraying_from_one_ip_is_throttled_across_emails(): void
    {
        $target = User::factory()->admin()->create();

        foreach (range(1, 20) as $attempt) {
            $this->post(self::LOGIN, ['email' => "victima{$attempt}@ivea.gob.mx", 'password' => 'Verano2026']);
        }

        $this->from(self::LOGIN)
            ->post(self::LOGIN, ['email' => $target->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function authenticated_users_are_redirected_away_from_the_login_page(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(self::LOGIN)
            ->assertRedirect(route('dashboard'));
    }
}
