<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const string FORGOT = '/forgot-password';

    private const string RESET = '/reset-password';

    private const string NEW_PASSWORD = 'NuevaClave2026';

    private const string LINK_SENT = 'Si el correo pertenece a una cuenta activa del portal, '
        .'recibirás un enlace para restablecer tu contraseña.';

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    #[Test]
    public function renders_the_forgot_password_page(): void
    {
        $this->get(self::FORGOT)
            ->assertOk()
            ->assertSee('Recuperar Contraseña')
            ->assertSee(route('login'), false);
    }

    #[Test]
    public function renders_the_reset_password_page_with_the_email_prefilled(): void
    {
        $this->get(self::RESET.'/token-de-prueba?email=tutor%40ivea.gob.mx')
            ->assertOk()
            ->assertSee('Restablecer Contraseña')
            ->assertSee('tutor@ivea.gob.mx', false)
            ->assertSee('token-de-prueba', false);
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
    public function active_portal_users_receive_a_reset_link(string $role): void
    {
        $user = User::factory()->{$role}()->create();

        $this->from(self::FORGOT)
            ->post(self::FORGOT, ['email' => $user->email])
            ->assertRedirect(self::FORGOT)
            ->assertSessionHas('status', self::LINK_SENT);

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user): bool {
                $mail = $notification->toMail($user);

                return $mail->subject === 'Restablece tu contraseña — IVEA Analítica'
                    && str_contains((string) $mail->actionUrl, '/reset-password/'.$notification->token);
            },
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function ineligibleAccounts(): array
    {
        return ['student' => ['student'], 'inactive' => ['inactive'], 'unknown' => ['unknown']];
    }

    #[Test]
    #[DataProvider('ineligibleAccounts')]
    public function ineligible_accounts_get_the_same_answer_and_no_email(string $kind): void
    {
        $email = match ($kind) {
            'student' => User::factory()->student()->create()->email,
            'inactive' => User::factory()->admin()->inactive()->create()->email,
            'unknown' => 'nadie@ivea.gob.mx',
        };

        $this->from(self::FORGOT)
            ->post(self::FORGOT, ['email' => $email])
            ->assertSessionHas('status', self::LINK_SENT)
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    #[Test]
    public function repeated_requests_within_the_broker_throttle_get_the_same_answer(): void
    {
        $user = User::factory()->admin()->create();

        $this->post(self::FORGOT, ['email' => $user->email]);
        $this->from(self::FORGOT)
            ->post(self::FORGOT, ['email' => $user->email])
            ->assertSessionHas('status', self::LINK_SENT)
            ->assertSessionHasNoErrors();

        Notification::assertSentToTimes($user, ResetPasswordNotification::class, 1);
    }

    #[Test]
    public function forgot_password_requires_a_valid_email(): void
    {
        $this->from(self::FORGOT)
            ->post(self::FORGOT, ['email' => 'no-es-correo'])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_is_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post(self::FORGOT, ['email' => 'nadie@ivea.gob.mx'])->assertRedirect();
        }

        $this->post(self::FORGOT, ['email' => 'nadie@ivea.gob.mx'])->assertTooManyRequests();
    }

    #[Test]
    public function a_valid_token_resets_the_password(): void
    {
        $user = User::factory()->tutor()->create(['remember_token' => 'token-viejo']);
        $token = Password::createToken($user);

        $this->post(self::RESET, $this->resetPayload($user->email, $token))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Tu contraseña fue restablecida. Ya puedes iniciar sesión.');

        $user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->password));
        $this->assertNotSame('token-viejo', $user->remember_token);
        $this->assertGuest();
    }

    #[Test]
    public function a_reset_revokes_open_sessions_and_api_tokens(): void
    {
        $user = User::factory()->tutor()->create();
        $user->createToken('mobile');
        DB::table('sessions')->insert([
            'id' => 'sesion-robada',
            'user_id' => $user->id,
            'payload' => '',
            'last_activity' => now()->getTimestamp(),
        ]);
        $token = Password::createToken($user);

        $this->post(self::RESET, $this->resetPayload($user->email, $token))->assertRedirect(route('login'));

        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertSame(0, $user->tokens()->count());
    }

    #[Test]
    public function the_new_password_works_for_login(): void
    {
        $user = User::factory()->admin()->create();
        $token = Password::createToken($user);

        $this->post(self::RESET, $this->resetPayload($user->email, $token));

        $this->post('/login', ['email' => $user->email, 'password' => self::NEW_PASSWORD])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function a_token_cannot_be_used_twice(): void
    {
        $user = User::factory()->admin()->create();
        $token = Password::createToken($user);

        $this->post(self::RESET, $this->resetPayload($user->email, $token));

        $this->from(self::RESET.'/'.$token)
            ->post(self::RESET, [...$this->resetPayload($user->email, $token), 'password' => 'OtraClave2026',
                'password_confirmation' => 'OtraClave2026'])
            ->assertSessionHasErrors(['email' => 'El enlace de restablecimiento no es válido o ha expirado.']);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $user->refresh()->password));
    }

    #[Test]
    public function an_invalid_token_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        $this->from(self::RESET.'/inventado')
            ->post(self::RESET, $this->resetPayload($user->email, 'inventado'))
            ->assertSessionHasErrors(['email' => 'El enlace de restablecimiento no es válido o ha expirado.']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    #[Test]
    public function a_token_for_another_email_is_rejected(): void
    {
        $owner = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        $token = Password::createToken($owner);

        $this->from(self::RESET.'/'.$token)
            ->post(self::RESET, $this->resetPayload($other->email, $token))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $other->refresh()->password));
    }

    #[Test]
    public function a_student_cannot_reset_through_the_portal(): void
    {
        $student = User::factory()->student()->create();
        $token = Password::createToken($student);

        $this->from(self::RESET.'/'.$token)
            ->post(self::RESET, $this->resetPayload($student->email, $token))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $student->refresh()->password));
    }

    #[Test]
    public function weak_passwords_are_rejected(): void
    {
        $user = User::factory()->admin()->create();
        $token = Password::createToken($user);

        $this->from(self::RESET.'/'.$token)
            ->post(self::RESET, [...$this->resetPayload($user->email, $token), 'password' => 'corta',
                'password_confirmation' => 'corta'])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function password_confirmation_must_match(): void
    {
        $user = User::factory()->admin()->create();
        $token = Password::createToken($user);

        $this->from(self::RESET.'/'.$token)
            ->post(self::RESET, [...$this->resetPayload($user->email, $token), 'password_confirmation' => 'Otra2026xx'])
            ->assertSessionHasErrors('password');
    }

    /**
     * @return array<string, string>
     */
    private function resetPayload(string $email, string $token): array
    {
        return [
            'token' => $token,
            'email' => $email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ];
    }
}
