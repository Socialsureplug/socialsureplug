<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $afterColumn = [
        'number_orders'  => 'price',
        'renting_orders' => 'price',
        'selling_orders' => 'total_amount',
        'smm_orders'     => 'charge',
    ];

    public function up(): void
    {
        foreach ($this->afterColumn as $table => $after) {
            Schema::table($table, function (Blueprint $blueprint) use ($after) {
                $blueprint->decimal('cost', 14, 4)->nullable()->after($after)
                    ->comment('Provider cost snapshot at purchase time, in site currency. NULL for orders placed before this column existed.');
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->afterColumn) as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('cost');
            });
        }
    }
};
