<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promoter_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('requested'); // requested | paid | rejected
            $table->string('method')->nullable();           // kart/hesab nömrəsi və s.
            $table->text('note')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['promoter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promoter_payouts');
    }
};
