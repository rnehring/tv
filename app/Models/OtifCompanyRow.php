<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtifCompanyRow extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'otif_snapshot_id', 'company_code', 'company_name',
        'shipped_total', 'shipped_on_time', 'prev_shipped_total', 'prev_shipped_on_time',
        'open_orders', 'past_due', 'due_today',
        'd1_7', 'd8_14', 'd15_21', 'd22_28', 'd29_plus', 'sort_order',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(OtifSnapshot::class);
    }

    // ---- OTIF headline: on-time shipped ÷ total shipped ------------------

    public function otifPercent(): ?float
    {
        return $this->shipped_total > 0
            ? $this->shipped_on_time / $this->shipped_total * 100
            : null;
    }

    public function prevOtifPercent(): ?float
    {
        return $this->prev_shipped_total > 0
            ? $this->prev_shipped_on_time / $this->prev_shipped_total * 100
            : null;
    }

    /** Change in on-time percentage points vs last month (null if unknown). */
    public function deltaPoints(): ?float
    {
        $now = $this->otifPercent();
        $prev = $this->prevOtifPercent();

        return ($now !== null && $prev !== null) ? $now - $prev : null;
    }

    public function hasShipments(): bool
    {
        return $this->shipped_total > 0;
    }

    // ---- Backlog (secondary panel) --------------------------------------

    /** Ship-window buckets in display order: [label, count, css-key]. */
    public function buckets(): array
    {
        $buckets = [
            ['Past due', $this->past_due, 'pastdue'],
            ['Due today', $this->due_today, 'today'],
            ['1–7 days', $this->d1_7, 'd1'],
            ['8–14 days', $this->d8_14, 'd2'],
            ['15–21 days', $this->d15_21, 'd3'],
            ['22–28 days', $this->d22_28, 'd4'],
            ['29+ days', $this->d29_plus, 'd5'],
        ];

        $noDate = $this->open_orders - array_sum(array_column($buckets, 1));
        if ($noDate > 0) {
            $buckets[] = ['No ship date', $noDate, 'nodate'];
        }

        return $buckets;
    }
}
