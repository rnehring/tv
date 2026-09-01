<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GasChart extends Model
{
    protected $fillable = [
        'year_month', 'updated_on', 'finalized',
        'goal50', 'goal100', 'goal150', 'goal200', 'achieved',
    ];

    protected function casts(): array
    {
        return [
            'updated_on' => 'date',
            'finalized' => 'boolean',
            'goal50' => 'decimal:2',
            'goal100' => 'decimal:2',
            'goal150' => 'decimal:2',
            'goal200' => 'decimal:2',
            'achieved' => 'decimal:2',
        ];
    }

    /** The most recent month that actually has goals entered. */
    public static function latestWithGoals(): ?self
    {
        return static::where('goal50', '>', 0)
            ->orderByDesc('year_month')
            ->first();
    }

    public function monthLabel(): string
    {
        return Carbon::createFromFormat('Y-m', $this->year_month)->format('F');
    }

    public function year(): int
    {
        return (int) substr($this->year_month, 0, 4);
    }

    /** Tiers that have a value, as [50 => amount, 100 => amount, ...]. */
    public function tiers(): array
    {
        $tiers = [];
        foreach ([50, 100, 150, 200] as $t) {
            $v = (float) $this->{"goal$t"};
            if ($v > 0) {
                $tiers[$t] = $v;
            }
        }

        return $tiers;
    }

    public function topGoal(): float
    {
        $tiers = $this->tiers();

        return $tiers ? max($tiers) : 0.0;
    }

    /** Highest gift-card level currently reached (0 = none yet). */
    public function earnedLevel(): int
    {
        $earned = 0;
        foreach ($this->tiers() as $t => $v) {
            if ((float) $this->achieved >= $v) {
                $earned = $t;
            }
        }

        return $earned;
    }

    public function percentOfTop(): float
    {
        $top = $this->topGoal();

        return $top > 0 ? min(100, (float) $this->achieved / $top * 100) : 0.0;
    }

    /** Business days (Mon–Fri) in this chart's month. */
    public function totalWorkdays(): int
    {
        $start = Carbon::createFromFormat('Y-m', $this->year_month)->startOfMonth();

        return $this->countWeekdays($start, $start->copy()->endOfMonth());
    }

    /** Business days elapsed as of the "updated" date (capped to the month). */
    public function workdaysDone(): int
    {
        $start = Carbon::createFromFormat('Y-m', $this->year_month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $asOf = $this->updated_on ? $this->updated_on->copy() : now();
        if ($asOf->lt($start)) {
            return 0;
        }
        if ($asOf->gt($end)) {
            $asOf = $end;
        }

        return $this->countWeekdays($start, $asOf);
    }

    /** Where sales "should" be by now, given workday pace. */
    public function paceAmount(): float
    {
        $total = $this->totalWorkdays();

        return $total > 0 ? $this->workdaysDone() / $total * $this->topGoal() : 0.0;
    }

    public function isAheadOfPace(): bool
    {
        return (float) $this->achieved >= $this->paceAmount();
    }

    public function dailyActual(): float
    {
        $done = $this->workdaysDone();

        return $done > 0 ? (float) $this->achieved / $done : 0.0;
    }

    /** Daily sales needed to hit a given tier over the whole month. */
    public function dailyTarget(int $tier): float
    {
        $total = $this->totalWorkdays();

        return $total > 0 ? (float) $this->{"goal$tier"} / $total : 0.0;
    }

    protected function countWeekdays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }
}
