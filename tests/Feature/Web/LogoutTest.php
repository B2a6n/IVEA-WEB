<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private const string LOGOUT = '/logout';

    #[Test]
    public function logout_ends_the_session_and_redirects_to_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(self::LOGOUT)
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    #[Test]
    public function logout_regenerates_the_csrf_token(): void
    {
        $this->actingAs(User::factory()->tutor()->create())->withSession(['_token' => 'old-token']);

        $this->post(self::LOGOUT);

        $this->assertNotSame('old-token', session()->token());
    }

    #[Test]
    public function logout_does_not_accept_get(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(self::LOGOUT)
            ->assertMethodNotAllowed();

        $this->assertAuthenticated();
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->post(self::LOGOUT)->assertRedirect(route('login'));
    }
}
