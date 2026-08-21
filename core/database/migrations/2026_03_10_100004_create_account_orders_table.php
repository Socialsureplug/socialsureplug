<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 16, 2);
            $table->string('status', 30)->default('paid'); // paid
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_orders');
    }
};
