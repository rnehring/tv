<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtifSnapshot extends Model
{
    protected $fillable = ['captured_at', 'source', 'error'];

    protected function casts(): array
    {
        return ['captured_at' => 'datetime'];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(OtifCompanyRow::class)->orderBy('sort_order');
    }

    /**
     * The most recent usable snapshot. Staleness is advisory — we still show the
     * last good data rather than a blank slide, but the view can flag its age.
     */
    public static function latestFresh(): ?self
    {
        return static::with('companies')
            ->whereNull('error')
            ->orderByDesc('captured_at')
            ->first();
    }

    public function isStale(): bool
    {
        $maxAge = (int) config('otif.max_age_minutes', 60);

        return $this->captured_at->lt(now()->subMinutes($maxAge));
    }

    /** Company-wide roll-up across every business unit ("All Orders"). */
    public function allOrders(): array
    {
        $rows = $this->companies;
        $total = $rows->sum('shipped_total');
        $onTime = $rows->sum('shipped_on_time');
        $prevTotal = $rows->sum('prev_shipped_total');
        $prevOnTime = $rows->sum('prev_shipped_on_time');

        $pct = $total > 0 ? $onTime / $total * 100 : null;
        $prevPct = $prevTotal > 0 ? $prevOnTime / $prevTotal * 100 : null;

        return [
            'shipped_total' => $total,
            'shipped_on_time' => $onTime,
            'otif' => $pct,
            'prev_otif' => $prevPct,
            'delta' => ($pct !== null && $prevPct !== null) ? $pct - $prevPct : null,
        ];
    }
}
