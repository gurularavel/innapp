<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('period');           // monthly | annual
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('kapitalbank_order_id')->nullable();
            $table->string('kapitalbank_order_password')->nullable();
            $table->string('status')->default('pending'); // pending | paid | failed
            $table->timestamps();

            $table->index('kapitalbank_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
