<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

final class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function running_twice_keeps_a_single_admin(): void
    {
        config(['ivea.seed_admin.password' => 'clave-segura-123']);

        $this->seed(AdminSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->assertSame(1, User::query()->where('email', config('ivea.seed_admin.email'))->count());
    }

    #[Test]
    public function production_requires_an_explicit_password(): void
    {
        config(['ivea.seed_admin.password' => null]);
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);

        // Invoked directly: db:seed would stop at its production confirmation prompt.
        $this->app->make(AdminSeeder::class)->run();
    }
}
