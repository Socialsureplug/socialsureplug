<?php

namespace Database\Seeders;

use App\Models\TopService;
use Illuminate\Database\Seeder;

class TopServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data from SMS SQL - top_services (one featured: Telegram UK).
     */
    public function run(): void
    {
        if (TopService::exists()) {
            return;
        }

        TopService::create([
            'server_id' => '282',
            'service_id' => 'tg',
            'status' => '1',
        ]);
    }
}
