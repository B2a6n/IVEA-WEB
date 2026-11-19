<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ConfigVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Two published configurations plus one draft pending publication.
 */
final class ConfigVersionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin)->firstOrFail();

        ConfigVersion::factory()->for($admin, 'publisher')->create([
            'version' => 1,
            'payload' => [
                'min_app_version' => '1.0.0',
                'sync_interval_minutes' => 60,
                'max_offline_days' => 7,
                'audio_hints_enabled' => true,
            ],
            'notes' => 'Configuración inicial del piloto.',
            'published_at' => now()->subDays(90),
        ]);

        ConfigVersion::factory()->for($admin, 'publisher')->create([
            'version' => 2,
            'payload' => [
                'min_app_version' => '1.1.0',
                'sync_interval_minutes' => 30,
                'max_offline_days' => 14,
                'audio_hints_enabled' => true,
            ],
            'notes' => 'Más días sin conexión para comunidades rurales.',
            'published_at' => now()->subDays(40),
        ]);

        ConfigVersion::factory()->draft()->create([
            'version' => 3,
            'payload' => [
                'min_app_version' => '1.2.0',
                'sync_interval_minutes' => 15,
                'max_offline_days' => 14,
                'audio_hints_enabled' => true,
            ],
            'notes' => 'Borrador: sincronización más frecuente.',
        ]);
    }
}
