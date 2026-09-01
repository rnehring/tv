<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MonthlyBackground extends Model
{
    public const KIND_BIRTHDAY = 'birthday';
    public const KIND_ANNIVERSARY = 'anniversary';

    protected $fillable = [
        'month', 'kind', 'image_path', 'heading',
        'text_color', 'accent_color', 'align',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public static function for(int $month, string $kind): ?self
    {
        return static::where('month', $month)->where('kind', $kind)->first();
    }
}
