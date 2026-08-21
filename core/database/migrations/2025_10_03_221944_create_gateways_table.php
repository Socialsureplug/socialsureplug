<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateways', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_name')->unique();
            $table->string('gateway_image')->nullable();
            $table->json('gateway_parameters')->nullable();
            $table->tinyInteger('gateway_type')->default(1)->comment('0=manual, 1=auto');
            $table->json('user_proof_param')->nullable();
            $table->decimal('rate', 16, 8)->default(1);
            $table->decimal('charge', 16, 8)->default(0);
            $table->tinyInteger('status')->default(0)->comment('0=off, 1=on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};