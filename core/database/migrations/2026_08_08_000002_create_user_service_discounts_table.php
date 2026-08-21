<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_service_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('service');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_service_discounts');
    }
};
