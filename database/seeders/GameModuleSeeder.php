<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GameModule;
use Illuminate\Database\Seeder;

final class GameModuleSeeder extends Seeder
{
    /**
     * @var list<array{
     *     slug: string, title: string, description: string,
     *     subject_area: string, difficulty_level: int, is_active: bool
     * }>
     */
    private const array MODULES = [
        [
            'slug' => 'reconoce-letras',
            'title' => 'Reconoce las letras',
            'description' => 'Identificación de vocales y consonantes con apoyo de imágenes y audio.',
            'subject_area' => 'lectoescritura',
            'difficulty_level' => 1,
            'is_active' => true,
        ],
        [
            'slug' => 'forma-palabras',
            'title' => 'Forma palabras',
            'description' => 'Construcción de palabras a partir de sílabas de uso cotidiano.',
            'subject_area' => 'lectoescritura',
            'difficulty_level' => 2,
            'is_active' => true,
        ],
        [
            'slug' => 'comprension-lectora',
            'title' => 'Comprensión lectora',
            'description' => 'Lectura de textos breves con preguntas de comprensión.',
            'subject_area' => 'lectoescritura',
            'difficulty_level' => 3,
            'is_active' => true,
        ],
        [
            'slug' => 'sumas-y-restas',
            'title' => 'Sumas y restas',
            'description' => 'Operaciones básicas con números de hasta tres cifras.',
            'subject_area' => 'matematicas',
            'difficulty_level' => 1,
            'is_active' => true,
        ],
        [
            'slug' => 'multiplicacion',
            'title' => 'Multiplicación',
            'description' => 'Tablas de multiplicar y problemas de reparto.',
            'subject_area' => 'matematicas',
            'difficulty_level' => 2,
            'is_active' => true,
        ],
        [
            'slug' => 'fracciones-cotidianas',
            'title' => 'Fracciones en la vida diaria',
            'description' => 'Medios, tercios y cuartos aplicados a recetas y compras.',
            'subject_area' => 'matematicas',
            'difficulty_level' => 3,
            'is_active' => true,
        ],
        [
            'slug' => 'manejo-del-dinero',
            'title' => 'Manejo del dinero',
            'description' => 'Cálculo de cambio y presupuesto familiar con billetes y monedas.',
            'subject_area' => 'vida_cotidiana',
            'difficulty_level' => 2,
            'is_active' => true,
        ],
        [
            'slug' => 'lectura-de-recibos',
            'title' => 'Lectura de recibos',
            'description' => 'Interpretación de recibos de luz, agua y tickets de compra.',
            'subject_area' => 'vida_cotidiana',
            'difficulty_level' => 2,
            'is_active' => true,
        ],
        [
            'slug' => 'geometria-basica',
            'title' => 'Geometría básica',
            'description' => 'Figuras, perímetros y áreas en espacios del hogar.',
            'subject_area' => 'matematicas',
            'difficulty_level' => 3,
            'is_active' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::MODULES as $index => $module) {
            GameModule::factory()->create([...$module, 'sort_order' => $index + 1]);
        }
    }
}
