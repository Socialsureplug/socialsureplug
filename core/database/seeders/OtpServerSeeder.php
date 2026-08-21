<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtpServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Data from SMS SQL - otp_server. api_id set to 1 (first ApiDetail).
     */
    public function run(): void
    {
        if (DB::table('otp_servers')->exists()) {
            return;
        }

        $apiId = 1;
        $servers = [
            [283, 'United Kingdom', '16', $apiId, '1'],
            [286, 'United States', '12', $apiId, '1'],
            [282, 'Canada', '36', $apiId, '1'],
            [287, 'UAE', '95', $apiId, '1'],
            [288, 'Saudi Arabia', '53', $apiId, '1'],
            [284, 'Qatar', '111', $apiId, '1'],
            [285, 'Turkey', '62', $apiId, '1'],
            [328, 'Egypt', '21', $apiId, '1'],
            [334, 'Seychelles', '184', $apiId, '1'],
            [333, 'Grenada', '127', $apiId, '1'],
            [332, 'Slovakia', '141', $apiId, '1'],
            [331, 'Finland', '163', $apiId, '1'],
            [330, 'Ireland', '23', $apiId, '1'],
            [329, 'Hong Kong', '14', $apiId, '1'],
            [326, 'Denmark', '172', $apiId, '1'],
            [327, 'Monaco', '144', $apiId, '1'],
            [318, 'Brazil', '73', $apiId, '1'],
            [325, 'Colombia', '33', $apiId, '1'],
            [324, 'China', '3', $apiId, '1'],
            [323, 'Sweden', '46', $apiId, '1'],
            [322, 'Puerto Rico', '97', $apiId, '1'],
            [321, 'Cape Verde', '186', $apiId, '1'],
            [320, 'Norway', '174', $apiId, '1'],
            [319, 'Argentinas', '39', $apiId, '1'],
            [317, 'Greece', '129', $apiId, '1'],
            [316, 'Mexico', '54', $apiId, '1'],
            [315, 'Peru', '65', $apiId, '1'],
            [314, 'Poland', '15', $apiId, '1'],
            [300, 'Singapore', '196', $apiId, '1'],
            [313, 'Portugal', '117', $apiId, '1'],
            [312, 'Ghana', '38', $apiId, '1'],
            [311, 'Thailand', '52', $apiId, '1'],
            [310, 'Romania', '32', $apiId, '1'],
            [309, 'Philippines', '4', $apiId, '1'],
            [308, 'Malaysia', '7', $apiId, '1'],
            [307, 'Ukraine', '1', $apiId, '1'],
            [306, 'Bangladesh', '60', $apiId, '1'],
            [305, 'South Africa', '31', $apiId, '1'],
            [304, 'Nigeria', '19', $apiId, '1'],
            [303, 'Korea', '1002', $apiId, '1'],
            [302, 'India', '22', $apiId, '1'],
            [301, 'Indonesia', '6', $apiId, '1'],
            [299, 'New Zealand', '67', $apiId, '1'],
            [295, 'Austria', '50', $apiId, '1'],
            [298, 'Germany', '43', $apiId, '1'],
            [297, 'Morocco', '37', $apiId, '1'],
            [296, 'Russia', '0', $apiId, '1'],
            [293, 'Netherlands', '48', $apiId, '1'],
            [294, 'Australia', '175', $apiId, '1'],
            [291, 'France', '78', $apiId, '1'],
            [292, 'Italy', '86', $apiId, '1'],
            [290, 'Spain', '56', $apiId, '1'],
            [289, 'Vietnam', '10', $apiId, '1'],
            [278, 'Paraguay', '87', $apiId, '1'],
        ];

        $rows = [];
        foreach ($servers as $s) {
            $rows[] = [
                'id' => $s[0],
                'server_name' => $s[1],
                'server_code' => $s[2],
                'provider_server_id' => $s[0],
                'api_id' => $s[3],
                'status' => $s[4],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('otp_servers')->insert($rows);

        // Set auto_increment above max id so future inserts don't conflict
        DB::statement('ALTER TABLE otp_servers AUTO_INCREMENT = 335');
    }
}
