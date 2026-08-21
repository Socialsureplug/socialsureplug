<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_icons', function (Blueprint $table) {
            $table->id();
            $table->string('short_code');
            $table->string('img_url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_icons');
    }
};