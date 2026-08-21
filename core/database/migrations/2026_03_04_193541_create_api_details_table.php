<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_details', function (Blueprint $table) {
            $table->id();
            $table->string('api_name');
            $table->string('api_url');
            $table->string('api_key');
            // 'handler' => legacy PHP handler_api.php, 'laravel' => /api/v1/* style
            $table->string('provider_type', 20)->default('handler');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_details');
    }
};