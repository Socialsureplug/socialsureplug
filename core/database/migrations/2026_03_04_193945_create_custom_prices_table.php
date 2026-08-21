<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('discount');
            $table->string('service_id');
            $table->string('server_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_prices');
    }
};