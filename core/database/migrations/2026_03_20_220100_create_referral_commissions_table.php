<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->decimal('deposit_amount', 16, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->decimal('commission_amount', 16, 2);
            $table->string('reference')->nullable();
            $table->enum('status', ['paid', 'cancelled'])->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique('payment_id');
            $table->index(['referrer_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
