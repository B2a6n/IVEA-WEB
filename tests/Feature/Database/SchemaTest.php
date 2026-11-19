<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function tableColumnsProvider(): array
    {
        return [
            'users' => ['users', ['role', 'group_id', 'is_active', 'last_login_at']],
            'groups' => ['groups', ['id', 'name', 'zone', 'tutor_id', 'is_active', 'created_at', 'updated_at']],
            'games_modules' => ['games_modules', [
                'id', 'slug', 'title', 'description', 'subject_area', 'difficulty_level', 'sort_order', 'is_active',
            ]],
            'user_progress_scores' => ['user_progress_scores', [
                'id', 'client_uuid', 'user_id', 'games_module_id', 'config_version_id', 'score', 'max_score',
                'duration_seconds', 'played_at', 'details_json',
            ]],
            'config_versions' => ['config_versions', [
                'id', 'version', 'payload', 'notes', 'published_by', 'published_at',
            ]],
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    #[Test]
    #[DataProvider('tableColumnsProvider')]
    public function table_has_expected_columns(string $table, array $columns): void
    {
        $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        $this->assertTrue(Schema::hasColumns($table, $columns), "Missing columns in {$table}");
    }

    #[Test]
    public function json_columns_use_jsonb(): void
    {
        $this->assertSame('jsonb', Schema::getColumnType('user_progress_scores', 'details_json'));
        $this->assertSame('jsonb', Schema::getColumnType('config_versions', 'payload'));
    }

    #[Test]
    public function client_uuid_is_a_uuid_column_with_unique_index(): void
    {
        $this->assertSame('uuid', Schema::getColumnType('user_progress_scores', 'client_uuid'));
        $this->assertTrue(Schema::hasIndex('user_progress_scores', ['client_uuid'], 'unique'));
    }

    /**
     * @return array<string, array{string, list<string>, string|null}>
     */
    public static function indexProvider(): array
    {
        return [
            'users.role' => ['users', ['role'], null],
            'users.group_id' => ['users', ['group_id'], null],
            'users.is_active' => ['users', ['is_active'], null],
            'groups.zone' => ['groups', ['zone'], null],
            'groups.tutor_id' => ['groups', ['tutor_id'], null],
            'groups.zone_name' => ['groups', ['zone', 'name'], 'unique'],
            'games_modules.slug' => ['games_modules', ['slug'], 'unique'],
            'games_modules.subject_area' => ['games_modules', ['subject_area'], null],
            'games_modules.sort_order' => ['games_modules', ['sort_order'], null],
            'games_modules.is_active' => ['games_modules', ['is_active'], null],
            'scores.user_played' => ['user_progress_scores', ['user_id', 'played_at'], null],
            'scores.module_played' => ['user_progress_scores', ['games_module_id', 'played_at'], null],
            'scores.played_at' => ['user_progress_scores', ['played_at'], null],
            'scores.config_version' => ['user_progress_scores', ['config_version_id'], null],
            'config_versions.version' => ['config_versions', ['version'], 'unique'],
            'config_versions.published_at' => ['config_versions', ['published_at'], null],
            'config_versions.published_by' => ['config_versions', ['published_by'], null],
        ];
    }

    /**
     * @param  list<string>  $columns
     */
    #[Test]
    #[DataProvider('indexProvider')]
    public function filtered_and_sorted_columns_are_indexed(string $table, array $columns, ?string $type): void
    {
        $this->assertTrue(
            Schema::hasIndex($table, $columns, $type),
            sprintf('Missing %s index on %s(%s)', $type ?? 'regular', $table, implode(', ', $columns)),
        );
    }

    #[Test]
    public function database_rejects_unknown_roles(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Rol Invalido',
            'email' => 'invalido@ivea.test',
            'password' => Hash::make('secret'),
            'role' => 'superuser',
        ]);
    }

    #[Test]
    public function database_rejects_negative_scores(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Educando',
            'email' => 'educando@ivea.test',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);
        $moduleId = DB::table('games_modules')->insertGetId([
            'slug' => 'sumas-basicas',
            'title' => 'Sumas basicas',
            'subject_area' => 'matematicas',
            'difficulty_level' => 1,
            'sort_order' => 1,
        ]);

        $this->expectException(QueryException::class);

        DB::table('user_progress_scores')->insert([
            'client_uuid' => '7b0e2a7e-4f43-4c7e-9a52-1f0b1a6a8c11',
            'user_id' => $userId,
            'games_module_id' => $moduleId,
            'score' => -5,
            'max_score' => 100,
            'duration_seconds' => 60,
            'played_at' => now(),
        ]);
    }
}
