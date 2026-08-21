<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'website_name' => 'My Website',
            'website_currency' => 'USD',
            'website_color' => '#4c45e0',
            'timezone' => 'UTC',
            'copyright_text' => '© 2025 My Website All Rights Reserved',
            'user_registration' => 1,
            'recaptcha' => 0,
            'user_email_notification' => 1,
            'admin_email_notification' => 1,
            'user_payment' => 1,
            'user_login' => 1,
            'buy_number' => 1,
            'boost_social' => 1,
            'social_logs' => 1,
        ]);
    }
}