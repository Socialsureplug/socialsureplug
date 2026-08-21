<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('number_id');
            $table->string('number');
            $table->string('server_id');
            $table->string('service_id');
            $table->string('order_id');
            $table->dateTime('buy_time');
            $table->string('service_price');
            $table->string('service_name');
            $table->string('status');
            $table->string('sms_text')->default('');
            $table->string('active_status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_numbers');
    }
};