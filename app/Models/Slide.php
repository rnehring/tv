<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_IFRAME = 'iframe';
    public const TYPE_BIRTHDAY = 'birthday';
    public const TYPE_ANNIVERSARY = 'anniversary';
    public const TYPE_GAS = 'gas';
    public const TYPE_OTIF = 'otif';

    protected $fillable = [
        'name', 'type', 'caption', 'image_path', 'iframe_url',
        'duration_ms', 'is_active', 'starts_on', 'ends_on', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'duration_ms' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class);
    }

    public function isGenerated(): bool
    {
        return in_array($this->type, [
            self::TYPE_BIRTHDAY, self::TYPE_ANNIVERSARY, self::TYPE_GAS, self::TYPE_OTIF,
        ], true);
    }

    /**
     * Public URL for an uploaded slide image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /** Active + within the display date window. */
    public function scopeLive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('starts_on')->orWhere('starts_on', '<=', $today);
            })
            ->where(function (Builder $q) use ($today) {
                $q->whereNull('ends_on')->orWhere('ends_on', '>=', $today);
            });
    }

    /** Limit to slides visible in the given location (or global slides). */
    public function scopeForLocation(Builder $query, ?Location $location): Builder
    {
        if (! $location) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($location) {
            $q->whereDoesntHave('locations')
                ->orWhereHas('locations', fn (Builder $l) => $l->where('locations.id', $location->id));
        });
    }
}
