<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LocationSeeder::class,
            MonthlyBackgroundSeeder::class,
            SlideSeeder::class,
            EmployeeSeeder::class,
            GasChartSeeder::class,
            OtifSeeder::class,
        ]);
    }
}
