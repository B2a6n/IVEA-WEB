<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates two tutors and three study circles per Veracruz zone, with students enrolled in each circle.
 */
final class ZoneGroupSeeder extends Seeder
{
    private const int TUTORS_PER_ZONE = 2;

    private const int MIN_STUDENTS_PER_GROUP = 14;

    private const int MAX_STUDENTS_PER_GROUP = 20;

    /**
     * Neighborhoods hosting a study circle, keyed by zone (must match Group::ZONES).
     *
     * @var array<string, list<string>>
     */
    private const array NEIGHBORHOODS = [
        'Xalapa' => ['Centro', 'Revolución', 'Las Ánimas'],
        'Veracruz' => ['Centro Histórico', 'Reserva Tarimoya', 'Formando Hogar'],
        'Córdoba' => ['San José', 'Las Estaciones', 'Venustiano Carranza'],
    ];

    public function run(): void
    {
        foreach (self::NEIGHBORHOODS as $zone => $neighborhoods) {
            $tutors = User::factory()->tutor()->count(self::TUTORS_PER_ZONE)->create();

            foreach ($neighborhoods as $index => $neighborhood) {
                $group = Group::factory()
                    ->inZone($zone)
                    ->for($tutors[$index % self::TUTORS_PER_ZONE], 'tutor')
                    ->create(['name' => "Círculo de Estudio {$neighborhood}"]);

                User::factory()
                    ->student()
                    ->for($group)
                    ->count(fake()->numberBetween(self::MIN_STUDENTS_PER_GROUP, self::MAX_STUDENTS_PER_GROUP))
                    ->sequence(fn () => ['is_active' => fake()->boolean(92)])
                    ->create();
            }
        }
    }
}
