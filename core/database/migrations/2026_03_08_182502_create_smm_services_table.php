<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('smm_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('smm_category_id')->constrained('smm_categories')->cascadeOnDelete();
            $table->unsignedBigInteger('api_provider_id')->nullable();
            $table->string('api_service_id')->nullable();
            $table->unsignedInteger('min')->default(1);
            $table->unsignedInteger('max')->default(10000);
            $table->decimal('price', 16, 4)->default(0);
            $table->decimal('original_price', 16, 4)->nullable();
            $table->string('type', 50)->default('default');
            $table->string('add_type', 20)->default('manual'); // manual | api
            $table->boolean('dripfeed')->default(false);
            $table->text('desc')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('api_provider_id')->references('id')->on('smm_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smm_services');
    }
};