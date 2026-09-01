<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBirthday extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'month', 'day',
        'department', 'source_key', 'imported_on',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'day' => 'integer',
            'imported_on' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** Is this birthday today? */
    public function isToday(): bool
    {
        return (int) $this->month === (int) now()->month
            && (int) $this->day === (int) now()->day;
    }
}
