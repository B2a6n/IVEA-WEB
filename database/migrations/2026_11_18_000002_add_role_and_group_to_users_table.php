<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default(UserRole::Student->value)->index();
            $table->foreignId('group_id')->nullable()->index()->constrained('groups')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestampTz('last_login_at')->nullable();
        });

        $roles = implode(', ', array_map(fn (string $role) => "'{$role}'", UserRole::values()));

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ({$roles}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};
