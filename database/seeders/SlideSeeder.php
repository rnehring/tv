<?php

namespace Database\Seeders;

use App\Models\Slide;
use Illuminate\Database\Seeder;

class SlideSeeder extends Seeder
{
    public function run(): void
    {
        // The two live-generated slides. Rendered from employee data and
        // auto-hidden when the current month has nobody.
        Slide::updateOrCreate(
            ['type' => Slide::TYPE_BIRTHDAY],
            ['name' => 'Birthdays this month', 'duration_ms' => 10000, 'is_active' => true, 'sort_order' => 10]
        );

        Slide::updateOrCreate(
            ['type' => Slide::TYPE_ANNIVERSARY],
            ['name' => 'Work anniversaries this month', 'duration_ms' => 10000, 'is_active' => true, 'sort_order' => 11]
        );

        Slide::updateOrCreate(
            ['type' => Slide::TYPE_GAS],
            ['name' => 'Gift card goal', 'duration_ms' => 12000, 'is_active' => true, 'sort_order' => 12]
        );

        Slide::updateOrCreate(
            ['type' => Slide::TYPE_OTIF],
            ['name' => 'Order backlog (OTIF)', 'duration_ms' => 14000, 'is_active' => true, 'sort_order' => 13]
        );
    }
}
