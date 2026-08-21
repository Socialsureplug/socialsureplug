<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name');
            $table->string('server_code');
            $table->string('provider_server_id')->nullable();
            $table->unsignedBigInteger('api_id');
            $table->string('status')->default('1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_servers');
    }
};