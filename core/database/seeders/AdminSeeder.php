<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = Admin::where('email', 'admin@gmail.com')->first();
        if (! $admin) {
            $admin = new Admin();
            $admin->name = 'Admin';
            $admin->username = 'admin';
            $admin->email = 'admin@gmail.com';
            $admin->password = 'password';
            $admin->save();
        }
    }
}