<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Seed product categories for account selling.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Instagram Accounts', 'sort' => 1],
            ['name' => 'TikTok Accounts', 'sort' => 2],
            ['name' => 'Facebook Accounts', 'sort' => 3],
            ['name' => 'Twitter / X Accounts', 'sort' => 4],
            ['name' => 'YouTube Accounts', 'sort' => 5],
            ['name' => 'Telegram Accounts', 'sort' => 6],
            ['name' => 'Other Accounts', 'sort' => 99],
        ];

        foreach ($categories as $item) {
            ProductCategory::firstOrCreate(
                ['name' => $item['name']],
                ['sort' => $item['sort'], 'status' => true]
            );
        }
    }
}
