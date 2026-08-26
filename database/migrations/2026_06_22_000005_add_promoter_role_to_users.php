<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MODIFY COLUMN / ENUM are MySQL specific. Other drivers (sqlite in tests)
        // already store this column as plain text, so there is nothing to widen.
        if (! $this->isMysql()) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'doctor', 'promoter') NOT NULL DEFAULT 'doctor'");
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'doctor') NOT NULL DEFAULT 'doctor'");
    }

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};
