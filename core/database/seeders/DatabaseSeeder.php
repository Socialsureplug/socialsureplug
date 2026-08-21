<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SettingSeeder::class,
            AdminEmailTemplateSeeder::class,
            UserEmailTemplateSeeder::class,
            ApiDetailSeeder::class,
            OtpServerSeeder::class,
            ServiceIconSeeder::class,
            ServiceSeeder::class,
            TopServiceSeeder::class,
            SmmCategorySeeder::class,
            SmmServiceSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            TutorialSeeder::class,
        ]);
    }
}
