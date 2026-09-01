<?php

namespace App\Services;

use App\Models\EmployeeAnniversary;
use App\Models\EmployeeBirthday;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Imports employee birthday / work-anniversary rows from the ADP-style payroll
 * exports the legacy app consumed.
 *
 * Birthday columns    : [Company Code?], Month, Birth Day, Home Department, Payroll Name
 * Anniversary columns : Month, [Company Code?], Home Department, Length of Service,
 *                        Payroll Name, Hire/Rehire Date
 *
 * "Payroll Name" is "Last, First". The optional leading company-code column and
 * header rows are auto-detected, so either variant of each export drops straight in.
 */
class AdpCsvImporter
{
    protected const MONTHS = [
        'january' => 1, 'jan' => 1,
        'february' => 2, 'feb' => 2, 'feburary' => 2, // legacy misspelling tolerated
        'march' => 3, 'mar' => 3,
        'april' => 4, 'apr' => 4,
        'may' => 5,
        'june' => 6, 'jun' => 6,
        'july' => 7, 'jul' => 7,
        'august' => 8, 'aug' => 8,
        'september' => 9, 'sep' => 9, 'sept' => 9,
        'october' => 10, 'oct' => 10,
        'november' => 11, 'nov' => 11,
        'december' => 12, 'dec' => 12,
    ];

    /**
     * @return array{imported:int,updated:int,skipped:int,errors:array<int,string>}
     */
    public function importBirthdays(string $path): array
    {
        $result = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($this->rows($path) as $line => $row) {
            $row = array_map(fn ($c) => trim((string) $c), $row);
            if ($this->isBlank($row)) {
                continue;
            }

            $month = $this->findMonth($row);
            $name = $this->findName($row);
            if ($month === null || $name === null) {
                $result['skipped']++;
                continue; // header or unparseable
            }

            $day = $this->findDay($row);
            if ($day === null) {
                $result['errors'][] = "Line {$line}: could not find a birth day.";
                $result['skipped']++;
                continue;
            }

            [$first, $last] = $name;
            $department = $this->findDepartment($row);
            $key = $this->key('b', $first, $last, $month, $day);

            $existing = EmployeeBirthday::where('source_key', $key)->first();
            EmployeeBirthday::updateOrCreate(
                ['source_key' => $key],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'month' => $month,
                    'day' => $day,
                    'department' => $department,
                    'imported_on' => now()->toDateString(),
                ]
            );
            $existing ? $result['updated']++ : $result['imported']++;
        }

        return $result;
    }

    /**
     * @return array{imported:int,updated:int,skipped:int,errors:array<int,string>}
     */
    public function importAnniversaries(string $path): array
    {
        $result = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        foreach ($this->rows($path) as $line => $row) {
            $row = array_map(fn ($c) => trim((string) $c), $row);
            if ($this->isBlank($row)) {
                continue;
            }

            $name = $this->findName($row);
            $hire = $this->findDate($row);
            if ($name === null || $hire === null) {
                $result['skipped']++;
                continue; // header or unparseable
            }

            // Prefer the explicit Month column; fall back to the hire month.
            $month = $this->findMonth($row) ?? (int) $hire->month;
            $day = (int) $hire->day;

            [$first, $last] = $name;
            $department = $this->findDepartment($row);
            $key = $this->key('a', $first, $last, $month, $day);

            $existing = EmployeeAnniversary::where('source_key', $key)->first();
            EmployeeAnniversary::updateOrCreate(
                ['source_key' => $key],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'month' => $month,
                    'day' => $day,
                    'hire_date' => $hire->toDateString(),
                    'department' => $department,
                    'imported_on' => now()->toDateString(),
                ]
            );
            $existing ? $result['updated']++ : $result['imported']++;
        }

        return $result;
    }

    /** Yields CSV rows keyed by 1-based line number. */
    protected function rows(string $path): \Generator
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        $line = 0;
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $line++;
            yield $line => $row;
        }
        fclose($handle);
    }

    protected function isBlank(array $row): bool
    {
        return count(array_filter($row, fn ($c) => $c !== '')) === 0;
    }

    /** First cell that is a recognisable month name. */
    protected function findMonth(array $row): ?int
    {
        foreach ($row as $cell) {
            $m = self::MONTHS[strtolower(trim($cell))] ?? null;
            if ($m !== null) {
                return $m;
            }
        }

        return null;
    }

    /** First cell shaped like "Last, First" -> [first, last]. */
    protected function findName(array $row): ?array
    {
        foreach ($row as $cell) {
            if (str_contains($cell, ',')) {
                $parts = explode(',', $cell, 2);
                $last = trim($parts[0]);
                $first = trim($parts[1] ?? '');
                if ($last !== '' && $first !== '' && ! is_numeric($last)) {
                    return [$first, $last];
                }
            }
        }

        return null;
    }

    /** A standalone integer 1-31 that isn't part of a date. */
    protected function findDay(array $row): ?int
    {
        foreach ($row as $cell) {
            if (preg_match('/^\d{1,2}$/', $cell)) {
                $n = (int) $cell;
                if ($n >= 1 && $n <= 31) {
                    return $n;
                }
            }
        }

        return null;
    }

    /** First cell parseable as a date (m/d/Y, Y-m-d, etc.). */
    protected function findDate(array $row): ?Carbon
    {
        foreach ($row as $cell) {
            $cell = trim($cell);
            if ($cell === '' || preg_match('/^\d{1,2}$/', $cell)) {
                continue;
            }
            if (! preg_match('#[/\-.]#', $cell)) {
                continue;
            }
            foreach (['m/d/Y', 'n/j/Y', 'Y-m-d', 'm-d-Y', 'm/d/y'] as $fmt) {
                try {
                    $d = Carbon::createFromFormat($fmt, $cell);
                    if ($d && $d->year > 1950 && $d->year < 2100) {
                        return $d->startOfDay();
                    }
                } catch (\Throwable) {
                    // try next format
                }
            }
        }

        return null;
    }

    /** The ADP "Home Department" code (a longish numeric string). */
    protected function findDepartment(array $row): ?string
    {
        foreach ($row as $cell) {
            if (preg_match('/^\d{4,}$/', trim($cell))) {
                return trim($cell);
            }
        }

        return null;
    }

    protected function key(string $prefix, string $first, string $last, int $month, int $day): string
    {
        return $prefix.':'.Str::slug($first.'-'.$last).":{$month}:{$day}";
    }
}
