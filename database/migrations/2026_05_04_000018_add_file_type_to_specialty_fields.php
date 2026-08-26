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

        DB::statement("ALTER TABLE specialty_fields MODIFY COLUMN type ENUM('text','number','date','select','textarea','photo','file') DEFAULT 'text'");
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::statement("ALTER TABLE specialty_fields MODIFY COLUMN type ENUM('text','number','date','select','textarea','photo') DEFAULT 'text'");
    }

    private function isMysql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};
