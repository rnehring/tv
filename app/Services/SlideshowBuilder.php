<?php

namespace App\Services;

use App\Models\EmployeeAnniversary;
use App\Models\EmployeeBirthday;
use App\Models\Location;
use App\Models\MonthlyBackground;
use App\Models\Slide;
use Illuminate\Support\Collection;

/**
 * Assembles the ordered list of renderable slides for the kiosk. Static slides
 * come straight from the database; the birthday/anniversary slides are expanded
 * from live employee data for the current month and are skipped entirely when
 * that month has no matching people (matching the legacy auto-hide behaviour).
 */
class SlideshowBuilder
{
    public function build(?Location $location): Collection
    {
        $slides = Slide::query()
            ->live()
            ->forLocation($location)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $slides
            ->map(fn (Slide $slide) => $this->render($slide))
            ->filter()
            ->values();
    }

    protected function render(Slide $slide): ?array
    {
        $base = [
            'id' => $slide->id,
            'type' => $slide->type,
            'name' => $slide->name,
            'duration_ms' => max(1000, (int) $slide->duration_ms),
            'caption' => $slide->caption,
        ];

        return match ($slide->type) {
            Slide::TYPE_IMAGE => $slide->image_url
                ? $base + ['image_url' => $slide->image_url]
                : null,
            Slide::TYPE_IFRAME => $slide->iframe_url
                ? $base + ['iframe_url' => $slide->iframe_url]
                : null,
            Slide::TYPE_BIRTHDAY => $this->birthday($base),
            Slide::TYPE_ANNIVERSARY => $this->anniversary($base),
            Slide::TYPE_GAS => $this->gas($base),
            Slide::TYPE_OTIF => $this->otif($base),
            default => null,
        };
    }

    protected function gas(array $base): ?array
    {
        $chart = \App\Models\GasChart::latestWithGoals();

        if (! $chart) {
            return null; // nothing to show yet
        }

        return $base + ['chart' => $chart];
    }

    protected function otif(array $base): ?array
    {
        $snapshot = \App\Models\OtifSnapshot::latestFresh();

        if (! $snapshot || $snapshot->companies->isEmpty()) {
            return null;
        }

        return $base + ['snapshot' => $snapshot];
    }

    protected function birthday(array $base): ?array
    {
        $month = (int) now()->month;

        $people = EmployeeBirthday::where('month', $month)
            ->orderBy('day')->orderBy('last_name')->get();

        if ($people->isEmpty()) {
            return null; // auto-hide when nobody this month
        }

        $bg = MonthlyBackground::for($month, MonthlyBackground::KIND_BIRTHDAY);

        return $base + [
            'month' => $month,
            'background_url' => $bg?->image_url,
            'text_color' => $bg->text_color ?? '#3a2416',
            'accent_color' => $bg->accent_color ?? '#c0392b',
            'heading' => $bg->heading ?? null,
            'align' => $bg->align ?? 'center',
            'font_size' => $bg->font_size ?? null,
            'entries' => $people->map(fn (EmployeeBirthday $p) => [
                'day' => $p->day,
                'day_label' => $this->ordinal($p->day),
                'name' => $p->full_name,
                'suffix' => '',
                'today' => $p->isToday(),
            ])->all(),
        ];
    }

    protected function anniversary(array $base): ?array
    {
        $month = (int) now()->month;

        $people = EmployeeAnniversary::where('month', $month)
            ->orderBy('day')->orderBy('last_name')->get();

        if ($people->isEmpty()) {
            return null;
        }

        $bg = MonthlyBackground::for($month, MonthlyBackground::KIND_ANNIVERSARY);

        return $base + [
            'month' => $month,
            'background_url' => $bg?->image_url,
            'text_color' => $bg->text_color ?? '#0d3b53',
            'accent_color' => $bg->accent_color ?? '#c0392b',
            'heading' => $bg->heading ?? null,
            'align' => $bg->align ?? 'center',
            'font_size' => $bg->font_size ?? null,
            'entries' => $people->map(fn (EmployeeAnniversary $p) => [
                'day' => $p->day,
                'day_label' => $this->ordinal($p->day),
                'name' => $p->full_name,
                'suffix' => ' ('.$p->years_label.')',
                'today' => $p->isToday(),
            ])->all(),
        ];
    }

    /**
     * Build a single birthday/anniversary slide for a given month, for the admin
     * font-size preview. Uses that month's real people; falls back to sample
     * names when the month is empty so the layout is still visible.
     */
    public function previewSlide(int $month, string $kind): array
    {
        $isAnniv = $kind === MonthlyBackground::KIND_ANNIVERSARY;
        $bg = MonthlyBackground::for($month, $kind);

        if ($isAnniv) {
            $people = EmployeeAnniversary::where('month', $month)->orderBy('day')->orderBy('last_name')->get();
            $entries = $people->map(fn (EmployeeAnniversary $p) => [
                'day' => $p->day, 'day_label' => $this->ordinal($p->day),
                'name' => $p->full_name, 'suffix' => ' ('.$p->years_label.')', 'today' => false,
            ])->all();
        } else {
            $people = EmployeeBirthday::where('month', $month)->orderBy('day')->orderBy('last_name')->get();
            $entries = $people->map(fn (EmployeeBirthday $p) => [
                'day' => $p->day, 'day_label' => $this->ordinal($p->day),
                'name' => $p->full_name, 'suffix' => '', 'today' => false,
            ])->all();
        }

        if (empty($entries)) {
            $samples = ['Alex Johnson', 'Maria Garcia', 'Chris Lee', 'Sam Patel', 'Jordan Smith', 'Taylor Brown',
                'Jamie Nguyen', 'Casey Davis', 'Riley Martin', 'Morgan Clark', 'Drew Adams', 'Quinn Rivera'];
            foreach ($samples as $i => $n) {
                $entries[] = ['day' => $i + 1, 'day_label' => $this->ordinal($i + 1), 'name' => $n,
                    'suffix' => $isAnniv ? ' (5)' : '', 'today' => false];
            }
        }

        return [
            'type' => $isAnniv ? 'anniversary' : 'birthday',
            'background_url' => $bg?->image_url,
            'text_color' => $bg->text_color ?? ($isAnniv ? '#0d3b53' : '#3a2416'),
            'accent_color' => $bg->accent_color ?? '#c0392b',
            'heading' => $bg->heading ?? null,
            'align' => $bg->align ?? 'center',
            'font_size' => $bg->font_size ?? null,
            'entries' => $entries,
        ];
    }

    protected function ordinal(int $n): string
    {
        $suffixes = ['th', 'st', 'nd', 'rd'];
        $v = $n % 100;

        return $n.($suffixes[($v - 20) % 10] ?? $suffixes[$v] ?? $suffixes[0]);
    }
}
