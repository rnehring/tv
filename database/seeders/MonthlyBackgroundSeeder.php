<?php

namespace Database\Seeders;

use App\Models\MonthlyBackground;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MonthlyBackgroundSeeder extends Seeder
{
    /**
     * Per-month name / "today" colours carried over from the legacy config so
     * the slides keep their seasonal palette.
     * [text_color, accent_color]
     */
    protected array $colors = [
        1 => ['#422020', '#ff0505'],
        2 => ['#b33a3a', '#0b7cd9'],
        3 => ['#246b14', '#d41e1e'],
        4 => ['#6e5fcf', '#530ceb'],
        5 => ['#d9272d', '#40910d'],
        6 => ['#58a81b', '#8a6d15'],
        7 => ['#1452a3', '#c72429'],
        8 => ['#2871a8', '#c85a0c'],
        9 => ['#613b1b', '#b35a12'],
        10 => ['#383838', '#b8680d'],
        11 => ['#706343', '#c0531f'],
        12 => ['#1b1b1b', '#c1121f'],
    ];

    public function run(): void
    {
        $source = database_path('seeders/assets/backgrounds');
        Storage::disk('public')->makeDirectory('backgrounds');

        foreach (range(1, 12) as $month) {
            foreach (['birthday', 'anniversary'] as $kind) {
                [$text, $accent] = $this->colors[$month];

                $imagePath = $this->copyAsset($source, $kind, $month);

                MonthlyBackground::updateOrCreate(
                    ['month' => $month, 'kind' => $kind],
                    [
                        'image_path' => $imagePath,
                        'heading' => null,
                        'text_color' => $kind === 'anniversary' && $month === 12 ? '#246b14' : $text,
                        'accent_color' => $accent,
                        'align' => 'center',
                    ]
                );
            }
        }
    }

    /** Copy a bundled seed background into the public disk; return its stored path. */
    protected function copyAsset(string $source, string $kind, int $month): ?string
    {
        foreach (['jpg', 'png', 'jpeg', 'webp'] as $ext) {
            $file = "{$source}/{$kind}-{$month}.{$ext}";
            if (File::exists($file)) {
                $dest = "backgrounds/{$kind}-{$month}.{$ext}";
                Storage::disk('public')->put($dest, File::get($file));

                return $dest;
            }
        }

        return null;
    }
}
