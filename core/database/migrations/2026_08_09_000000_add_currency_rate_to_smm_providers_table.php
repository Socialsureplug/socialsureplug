<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smm_providers', function (Blueprint $table) {
            $table->decimal('currency_rate', 12, 4)->default(1)->after('currency_code')
                ->comment('Units of site currency per 1 unit of this provider\'s billing currency. E.g. 1500 for a USD provider when the site runs in NGN, 1 for a same-currency provider.');
        });
    }

    public function down(): void
    {
        Schema::table('smm_providers', function (Blueprint $table) {
            $table->dropColumn('currency_rate');
        });
    }
};
