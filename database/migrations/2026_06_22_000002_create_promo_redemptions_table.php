<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
            $table->foreignId('promoter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_payment_id')->nullable()->constrained('subscription_payments')->nullOnDelete();

            $table->decimal('discount_applied', 10, 2)->default(0); // müştəriyə verilən endirim (AZN)
            $table->decimal('commission_amount', 10, 2)->default(0); // promotor komissiyası (AZN)

            // pending → available → paid ; cancelled (refund)
            $table->string('status')->default('pending');
            $table->timestamp('available_at')->nullable(); // bu tarixdən sonra çıxarıla bilər
            $table->foreignId('payout_id')->nullable();    // hansı çıxarışa daxil edilib

            $table->timestamps();

            $table->index(['promoter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_redemptions');
    }
};
