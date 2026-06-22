<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('promoter_id')->constrained('users')->cascadeOnDelete();

            // Müştəriyə tətbiq olunan endirim
            $table->string('discount_type')->default('percent');  // percent | fixed
            $table->decimal('discount_value', 10, 2)->default(0);

            // Promotora yazılan komissiya (ilk ödənişdə)
            $table->string('commission_type')->default('percent'); // percent | fixed
            $table->decimal('commission_value', 10, 2)->default(0);

            $table->unsignedInteger('max_uses')->nullable(); // null = limitsiz
            $table->unsignedInteger('used_count')->default(0);
            $table->date('expires_at')->nullable();          // null = müddətsiz
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('promoter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
