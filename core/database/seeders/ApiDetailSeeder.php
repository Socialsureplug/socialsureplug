<?php

namespace Database\Seeders;

use App\Models\ApiDetail;
use Illuminate\Database\Seeder;

class ApiDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (ApiDetail::exists()) {
            return;
        }

        ApiDetail::create([
            'api_name' => 'smsbower',
            'api_url' => 'https://radiumshop.cc/bower_amazon',
            'api_key' => 'h7Xmn8g6109WdR4FyxEjOVAxAozIJiiZ',
        ]);
    }
}
