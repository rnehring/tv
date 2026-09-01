<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'ip_pattern',
        'is_default', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function slides(): BelongsToMany
    {
        return $this->belongsToMany(Slide::class);
    }

    /**
     * Resolve the location for a request: explicit ?location=slug wins,
     * otherwise auto-detect by client IP prefix, otherwise the default zone.
     */
    public static function resolve(?string $slug, ?string $ip): ?self
    {
        $active = static::where('is_active', true)->orderBy('sort_order')->get();

        if ($slug) {
            $bySlug = $active->firstWhere('slug', $slug);
            if ($bySlug) {
                return $bySlug;
            }
        }

        if ($ip) {
            foreach ($active as $location) {
                if ($location->ip_pattern && str_starts_with($ip, $location->ip_pattern)) {
                    return $location;
                }
            }
        }

        return $active->firstWhere('is_default', true) ?? $active->first();
    }
}
