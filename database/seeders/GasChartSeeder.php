<?php

namespace Database\Seeders;

use App\Models\GasChart;
use Illuminate\Database\Seeder;

class GasChartSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Current month sample so the slide shows live data out of the box.
            ['year_month' => now()->format('Y-m'), 'updated_on' => now()->toDateString(), 'finalized' => false,
                'goal50' => 50000, 'goal100' => 100000, 'goal150' => 150000, 'goal200' => 200000, 'achieved' => 118000],
            // Ported historical figures from the legacy gasCharts table.
            ['year_month' => '2023-05', 'updated_on' => '2023-05-08', 'finalized' => false,
                'goal50' => 50000, 'goal100' => 100000, 'goal150' => 150000, 'goal200' => 200000, 'achieved' => 102000],
            ['year_month' => '2012-10', 'updated_on' => '2012-10-31', 'finalized' => true,
                'goal50' => 2800000, 'goal100' => 3200000, 'goal150' => 0, 'goal200' => 3800000, 'achieved' => 3832256],
            ['year_month' => '2012-11', 'updated_on' => '2012-11-11', 'finalized' => false,
                'goal50' => 2800000, 'goal100' => 3200000, 'goal150' => 0, 'goal200' => 3800000, 'achieved' => 692254],
        ];

        foreach ($rows as $row) {
            GasChart::updateOrCreate(['year_month' => $row['year_month']], $row);
        }
    }
}
