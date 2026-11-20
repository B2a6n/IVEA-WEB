<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EnsureWebRoleTest extends TestCase
{
    use RefreshDatabase;

    private const string ADMIN_ONLY = '/_test/admin-only';

    private const string SHARED = '/_test/shared';

    private const array METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    protected function setUp(): void
    {
        parent::setUp();

        // Test-only routes: the real write routes do not exist yet, and the middleware must not depend on them.
        Route::middleware(['web', 'auth', 'role:admin'])
            ->match(self::METHODS, self::ADMIN_ONLY, fn () => 'ok');
        Route::middleware(['web', 'auth', 'role:admin,tutor'])
            ->match(self::METHODS, self::SHARED, fn () => 'ok');

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function allMethods(): array
    {
        return array_combine(self::METHODS, array_map(fn (string $method) => [$method], self::METHODS));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function writeMethods(): array
    {
        return array_filter(self::allMethods(), fn (string $key) => $key !== 'GET', ARRAY_FILTER_USE_KEY);
    }

    #[Test]
    #[DataProvider('allMethods')]
    public function admin_can_read_and_write_admin_only_routes(string $method): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->call($method, self::ADMIN_ONLY)
            ->assertOk();
    }

    #[Test]
    #[DataProvider('allMethods')]
    public function tutor_is_forbidden_on_admin_only_routes(string $method): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->call($method, self::ADMIN_ONLY)
            ->assertForbidden();
    }

    #[Test]
    #[DataProvider('allMethods')]
    public function admin_can_read_and_write_shared_routes(string $method): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->call($method, self::SHARED)
            ->assertOk();
    }

    #[Test]
    public function tutor_can_read_shared_routes(): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->get(self::SHARED)
            ->assertOk();
    }

    #[Test]
    #[DataProvider('writeMethods')]
    public function tutor_is_read_only_on_shared_routes(string $method): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->call($method, self::SHARED)
            ->assertForbidden();
    }

    #[Test]
    public function student_is_forbidden_on_shared_routes(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->get(self::SHARED)
            ->assertForbidden();
    }

    #[Test]
    public function guest_is_redirected_to_login(): void
    {
        $this->get(self::ADMIN_ONLY)->assertRedirect(route('login'));
    }

    #[Test]
    public function forbidden_page_is_in_spanish(): void
    {
        $this->actingAs(User::factory()->tutor()->create())
            ->get(self::ADMIN_ONLY)
            ->assertForbidden()
            ->assertSee('Acceso denegado');
    }
}
