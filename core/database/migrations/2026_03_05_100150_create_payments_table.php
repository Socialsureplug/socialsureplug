<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // 'paystack','flutterwave','bank'
            $table->string('currency', 10)->default('NGN');
            $table->decimal('amount', 16, 2);
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])
                  ->default('pending');
            $table->string('channel')->nullable();          // card, bank_transfer etc
            $table->text('gateway_response')->nullable();   // raw JSON/string
            $table->string('proof_path')->nullable();       // bank transfer proof
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};