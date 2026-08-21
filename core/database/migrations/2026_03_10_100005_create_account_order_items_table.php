<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_order_id')->constrained('account_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_detail_id')->constrained('product_details')->cascadeOnDelete();
            $table->decimal('price', 16, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_order_items');
    }
};
