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
        // The Directorio de Educandos searches with ILIKE '%term%', which a btree index cannot serve.
        // On managed databases where the app role cannot create extensions, a DBA must enable pg_trgm first.
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE INDEX users_name_trgm_index ON users USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX users_email_trgm_index ON users USING gin (email gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The extension is left in place: other objects may depend on it.
        DB::statement('DROP INDEX IF EXISTS users_email_trgm_index');
        DB::statement('DROP INDEX IF EXISTS users_name_trgm_index');
    }
};
