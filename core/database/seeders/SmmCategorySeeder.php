<?php

namespace Database\Seeders;

use App\Models\SmmCategory;
use Illuminate\Database\Seeder;

class SmmCategorySeeder extends Seeder
{
    /**
     * Seed SMM categories (sample-like: name + sort).
     * Typical SMM panel categories for social platforms.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Instagram', 'sort' => 1],
            ['name' => 'TikTok', 'sort' => 2],
            ['name' => 'YouTube', 'sort' => 3],
            ['name' => 'Facebook', 'sort' => 4],
            ['name' => 'Twitter / X', 'sort' => 5],
            ['name' => 'Telegram', 'sort' => 6],
            ['name' => 'Spotify', 'sort' => 7],
            ['name' => 'Other', 'sort' => 99],
        ];

        foreach ($categories as $item) {
            SmmCategory::firstOrCreate(
                ['name' => $item['name']],
                ['sort' => $item['sort']]
            );
        }
    }
}
