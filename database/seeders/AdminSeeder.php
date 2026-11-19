<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

final class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('ivea.seed_admin.email');

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->info("El administrador {$email} ya existe; se omite.");

            return;
        }

        User::factory()->admin()->create([
            'name' => config('ivea.seed_admin.name'),
            'email' => $email,
            'password' => $this->resolvePassword(),
        ]);
    }

    private function resolvePassword(): string
    {
        $password = config('ivea.seed_admin.password');

        if (is_string($password) && $password !== '') {
            return $password;
        }

        if (app()->isProduction()) {
            throw new RuntimeException(
                'IVEA_ADMIN_PASSWORD es obligatorio para sembrar el administrador en producción.'
            );
        }

        $password = Str::password(16);
        $this->command?->warn("IVEA_ADMIN_PASSWORD no definido; contraseña generada: {$password}");

        return $password;
    }
}
