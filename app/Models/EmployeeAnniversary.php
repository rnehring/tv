<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAnniversary extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'month', 'day',
        'hire_date', 'department', 'source_key', 'imported_on',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'day' => 'integer',
            'hire_date' => 'date',
            'imported_on' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** Completed years of service as of this calendar year. */
    public function getYearsAttribute(): int
    {
        if (! $this->hire_date) {
            return 0;
        }

        return max(0, (int) now()->year - (int) $this->hire_date->year);
    }

    public function getYearsLabelAttribute(): string
    {
        $years = $this->years;

        return $years === 1 ? '1 year' : $years.' years';
    }

    public function isToday(): bool
    {
        return (int) $this->month === (int) now()->month
            && (int) $this->day === (int) now()->day;
    }
}
