<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The Directorio de Educandos aggregates every play per user (count, distinct modules, average, last
        // play). Covering those columns lets PostgreSQL answer it with an index-only scan.
        DB::statement(
            'CREATE INDEX user_progress_scores_directory_index ON user_progress_scores '
            .'(user_id, games_module_id) INCLUDE (score, max_score, played_at)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS user_progress_scores_directory_index');
    }
};
