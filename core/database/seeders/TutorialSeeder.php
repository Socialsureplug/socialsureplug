<?php

namespace Database\Seeders;

use App\Models\Tutorial;
use Illuminate\Database\Seeder;

class TutorialSeeder extends Seeder
{
    /**
     * Seed sample tutorials for the frontend accordion.
     */
    public function run(): void
    {
        $items = [
            ['title' => 'How to Buy a Number', 'url' => '#', 'sort_order' => 1],
            ['title' => 'How to Fund Your Wallet With Paystack', 'url' => '#', 'sort_order' => 2],
            ['title' => 'How to Fund Your Wallet With Flutterwave', 'url' => '#', 'sort_order' => 3],
        ];

        foreach ($items as $item) {
            Tutorial::firstOrCreate(
                ['title' => $item['title']],
                [
                    'url'        => $item['url'],
                    'status'     => 1,
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}
