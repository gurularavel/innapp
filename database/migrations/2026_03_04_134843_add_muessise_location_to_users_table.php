<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('muessise_unvani', 255)->nullable()->after('muessise_adi');
            $table->text('muessise_xerite')->nullable()->after('muessise_unvani');
            $table->string('muessise_xerite_code', 12)->nullable()->unique()->after('muessise_xerite');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['muessise_unvani', 'muessise_xerite', 'muessise_xerite_code']);
        });
    }
};
