<?php

namespace Database\Seeders;

use App\Models\OtifSnapshot;
use Illuminate\Database\Seeder;

/**
 * Seeds a sample OTIF snapshot so the slide renders before Epicor is wired up.
 * Figures are illustrative; `otif:fetch` replaces them with live ERP data.
 */
class OtifSeeder extends Seeder
{
    public function run(): void
    {
        OtifSnapshot::where('source', 'sample')->delete();

        $snapshot = OtifSnapshot::create([
            'captured_at' => now(),
            'source' => 'sample',
        ]);

        // code, name, [total, onTime], [prevTotal, prevOnTime], backlog[open,pd,dt,1-7,8-14,15-21,22-28,29+]
        $rows = [
            ['10',  'PureFlex',      210, 199, 195, 179, [203, 155, 0, 0, 0, 0, 0, 0]],
            ['20',  'Nil-Cor',        88,  76,  92,  84, [108, 79, 0, 0, 0, 0, 0, 0]],
            ['30',  'Ethylene',       64,  61,  60,  55, [311, 262, 0, 0, 0, 0, 0, 0]],
            ['40',  'Hills-McCanna', 142, 138, 150, 141, [0, 0, 0, 0, 0, 0, 0, 0]],
            ['50',  'RamParts',       47,  42,  51,  47, [0, 0, 0, 0, 0, 0, 0, 0]],
            ['PV0', 'PolyValve',      73,  62,  70,  61, [0, 0, 0, 0, 0, 0, 0, 0]],
            ['CC0', 'Conley',         38,  36,  41,  38, [0, 0, 0, 0, 0, 0, 0, 0]],
            ['GS0', 'Endurance',      29,  28,  33,  30, [0, 0, 0, 0, 0, 0, 0, 0]],
            ['A65', 'SAS',            55,  49,  58,  52, [225, 20, 0, 6, 22, 25, 26, 126]],
        ];

        foreach ($rows as $i => [$code, $name, $total, $onTime, $pTotal, $pOnTime, $bl]) {
            $snapshot->companies()->create([
                'company_code' => $code,
                'company_name' => $name,
                'shipped_total' => $total,
                'shipped_on_time' => $onTime,
                'prev_shipped_total' => $pTotal,
                'prev_shipped_on_time' => $pOnTime,
                'open_orders' => $bl[0],
                'past_due' => $bl[1],
                'due_today' => $bl[2],
                'd1_7' => $bl[3],
                'd8_14' => $bl[4],
                'd15_21' => $bl[5],
                'd22_28' => $bl[6],
                'd29_plus' => $bl[7],
                'sort_order' => $i,
            ]);
        }
    }
}
