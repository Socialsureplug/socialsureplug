<?php

namespace Database\Seeders;

use App\Models\SmmCategory;
use App\Models\SmmService;
use Illuminate\Database\Seeder;

class SmmServiceSeeder extends Seeder
{
    /**
     * Seed SMM services (manual/demo only – no API provider required).
     * Sample-like: name, category, min, max, price, type default, add_type manual.
     */
    public function run(): void
    {
        $categories = SmmCategory::orderBy('sort')->get()->keyBy('name');
        if ($categories->isEmpty()) {
            $this->command->warn('Run SmmCategorySeeder first.');
            return;
        }

        $services = [
            ['category' => 'Instagram', 'name' => 'Instagram Followers', 'min' => 100, 'max' => 100000, 'price' => 1.50],
            ['category' => 'Instagram', 'name' => 'Instagram Likes', 'min' => 50, 'max' => 50000, 'price' => 0.80],
            ['category' => 'Instagram', 'name' => 'Instagram Comments', 'min' => 10, 'max' => 5000, 'price' => 2.00],
            ['category' => 'TikTok', 'name' => 'TikTok Followers', 'min' => 100, 'max' => 100000, 'price' => 1.20],
            ['category' => 'TikTok', 'name' => 'TikTok Likes', 'min' => 50, 'max' => 50000, 'price' => 0.50],
            ['category' => 'TikTok', 'name' => 'TikTok Views', 'min' => 1000, 'max' => 1000000, 'price' => 0.30],
            ['category' => 'YouTube', 'name' => 'YouTube Views', 'min' => 1000, 'max' => 500000, 'price' => 2.50],
            ['category' => 'YouTube', 'name' => 'YouTube Likes', 'min' => 100, 'max' => 10000, 'price' => 3.00],
            ['category' => 'YouTube', 'name' => 'YouTube Subscribers', 'min' => 100, 'max' => 50000, 'price' => 5.00],
            ['category' => 'Facebook', 'name' => 'Facebook Page Likes', 'min' => 100, 'max' => 10000, 'price' => 2.00],
            ['category' => 'Facebook', 'name' => 'Facebook Post Likes', 'min' => 50, 'max' => 5000, 'price' => 1.00],
            ['category' => 'Twitter / X', 'name' => 'Twitter Followers', 'min' => 100, 'max' => 50000, 'price' => 2.20],
            ['category' => 'Twitter / X', 'name' => 'Twitter Likes', 'min' => 50, 'max' => 10000, 'price' => 1.50],
            ['category' => 'Telegram', 'name' => 'Telegram Channel Members', 'min' => 100, 'max' => 50000, 'price' => 1.80],
            ['category' => 'Spotify', 'name' => 'Spotify Plays', 'min' => 1000, 'max' => 100000, 'price' => 4.00],
        ];

        foreach ($services as $item) {
            $category = $categories->get($item['category']);
            if (!$category) {
                continue;
            }
            SmmService::firstOrCreate(
                [
                    'smm_category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'min' => $item['min'],
                    'max' => $item['max'],
                    'price' => $item['price'],
                    'type' => 'default',
                    'add_type' => 'manual',
                    'dripfeed' => false,
                    'status' => true,
                ]
            );
        }
    }
}
