<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Main / Office', 'slug' => 'main', 'description' => 'Default office TVs', 'ip_pattern' => null, 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Factory Floor', 'slug' => 'factory', 'description' => 'Shop-floor displays', 'ip_pattern' => null, 'is_default' => false, 'sort_order' => 2],
            ['name' => 'Kentwood', 'slug' => 'kentwood', 'description' => 'Kentwood location (10.x network)', 'ip_pattern' => '10.', 'is_default' => false, 'sort_order' => 3],
            ['name' => 'Houston', 'slug' => 'houston', 'description' => 'Houston location (192.x network)', 'ip_pattern' => '192.', 'is_default' => false, 'sort_order' => 4],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(['slug' => $loc['slug']], $loc + ['is_active' => true]);
        }
    }
}
