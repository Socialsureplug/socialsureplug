<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('smm_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('smm_category_id')->nullable()->constrained('smm_categories')->nullOnDelete();
            $table->foreignId('smm_service_id')->constrained('smm_services')->cascadeOnDelete();
            $table->string('link');
            $table->unsignedInteger('quantity');
            $table->decimal('charge', 16, 2);
            $table->string('status', 30)->default('awaiting'); // awaiting, inprogress, completed, partial, canceled, error, fail
            $table->unsignedBigInteger('api_provider_id')->nullable();
            $table->string('api_service_id')->nullable();
            $table->string('api_order_id')->nullable();
            $table->unsignedInteger('start_counter')->nullable();
            $table->unsignedInteger('remains')->nullable();
            $table->boolean('mode')->default(0); // 0 manual, 1 api
            $table->text('note')->nullable();
            $table->string('service_type', 50)->nullable();
            $table->text('comments')->nullable();
            $table->boolean('is_drip_feed')->default(false);
            $table->unsignedInteger('runs')->nullable();
            $table->unsignedInteger('interval')->nullable();
            $table->unsignedInteger('dripfeed_quantity')->nullable();
            $table->timestamps();

            $table->foreign('api_provider_id')->references('id')->on('smm_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smm_orders');
    }
};