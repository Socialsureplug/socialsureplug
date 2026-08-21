<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDetail;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed products with sample account details (credentials).
     */
    public function run(): void
    {
        $categories = ProductCategory::where('status', true)->orderBy('sort')->get()->keyBy('name');
        if ($categories->isEmpty()) {
            $this->command->warn('Run ProductCategorySeeder first.');
            return;
        }

        $products = [
            [
                'category' => 'Instagram Accounts',
                'name' => 'Instagram Account - Basic',
                'price' => 5.00,
                'description' => 'Single Instagram account. Login credentials provided after purchase.',
                'accounts' => [
                    'Username:demo_ig_1 | Password:DemoPass123',
                    'Username:demo_ig_2 | Password:DemoPass456',
                    'Username:demo_ig_3 | Password:DemoPass789',
                ],
            ],
            [
                'category' => 'Instagram Accounts',
                'name' => 'Instagram Account - Aged',
                'price' => 12.00,
                'description' => 'Aged Instagram account with some history.',
                'accounts' => [
                    'Username:aged_ig_1 | Password:AgedPass111',
                    'Username:aged_ig_2 | Password:AgedPass222',
                ],
            ],
            [
                'category' => 'TikTok Accounts',
                'name' => 'TikTok Account',
                'price' => 4.50,
                'description' => 'TikTok account ready to use.',
                'accounts' => [
                    'Username:demo_tt_1 | Password:TikTokPass1',
                    'Username:demo_tt_2 | Password:TikTokPass2',
                    'Username:demo_tt_3 | Password:TikTokPass3',
                    'Username:demo_tt_4 | Password:TikTokPass4',
                ],
            ],
            [
                'category' => 'Facebook Accounts',
                'name' => 'Facebook Account',
                'price' => 6.00,
                'description' => 'Facebook account with email access.',
                'accounts' => [
                    'Email:fb_demo1@example.com | Password:FBPass111',
                    'Email:fb_demo2@example.com | Password:FBPass222',
                ],
            ],
            [
                'category' => 'Twitter / X Accounts',
                'name' => 'Twitter / X Account',
                'price' => 7.00,
                'description' => 'Twitter (X) account credentials.',
                'accounts' => [
                    'Username:demo_tw_1 | Password:TwitterPass1',
                    'Username:demo_tw_2 | Password:TwitterPass2',
                    'Username:demo_tw_3 | Password:TwitterPass3',
                ],
            ],
        ];

        foreach ($products as $item) {
            $category = $categories->get($item['category']);
            if (!$category) {
                continue;
            }

            $product = Product::firstOrCreate(
                [
                    'product_category_id' => $category->id,
                    'name' => $item['name'],
                ],
                [
                    'price' => $item['price'],
                    'description' => $item['description'] ?? null,
                    'image' => null,
                    'status' => true,
                ]
            );

            $accounts = $item['accounts'] ?? [];
            foreach ($accounts as $details) {
                ProductDetail::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'details' => $details,
                    ],
                    ['is_sold' => false]
                );
            }
        }
    }
}
