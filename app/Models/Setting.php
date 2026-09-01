<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    /** All settings as an associative array. */
    public static function map(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }
}
