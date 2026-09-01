<?php

namespace App\Services;

use App\Models\OtifSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Pulls the OTIF scorecard from Epicor: per business unit, orders shipped
 * on-time vs total shipped for the reporting month and the prior month
 * (the on-time %), plus the current open backlog by ship window.
 *
 * Never throws to the caller — on failure it records the error and leaves the
 * previous good snapshot in place so the kiosk keeps showing data.
 */
class OtifFetcher
{
    public function fetch(): OtifSnapshot
    {
        $companies = config('otif.companies', []);

        $snapshot = OtifSnapshot::create([
            'captured_at' => now(),
            'source' => 'epicor',
        ]);

        try {
            $conn = DB::connection('epicor');

            // Reporting month: hold on the previous month until the 3rd of the
            // month so a fresh month doesn't show a misleadingly tiny sample.
            $report = now()->day <= 3 ? now()->copy()->subMonthNoOverflow() : now();
            $prev = $report->copy()->subMonthNoOverflow();

            $totalNow = $this->countByCompany($conn, config('otif.ship_query_total'), $report);
            $onTimeNow = $this->countByCompany($conn, config('otif.ship_query_on_time'), $report);
            $totalPrev = $this->countByCompany($conn, config('otif.ship_query_total'), $prev);
            $onTimePrev = $this->countByCompany($conn, config('otif.ship_query_on_time'), $prev);
            $backlog = $this->backlogByCompany($conn, config('otif.backlog_query'));

            foreach ($companies as $sort => $company) {
                $code = (string) $company['code'];
                $bl = (array) ($backlog[$code] ?? []);

                $snapshot->companies()->create([
                    'company_code' => $company['code'],
                    'company_name' => $company['name'],
                    'shipped_total' => $totalNow[$code] ?? 0,
                    'shipped_on_time' => $onTimeNow[$code] ?? 0,
                    'prev_shipped_total' => $totalPrev[$code] ?? 0,
                    'prev_shipped_on_time' => $onTimePrev[$code] ?? 0,
                    'open_orders' => (int) ($bl['open_orders'] ?? 0),
                    'past_due' => (int) ($bl['past_due'] ?? 0),
                    'due_today' => (int) ($bl['due_today'] ?? 0),
                    'd1_7' => (int) ($bl['d1_7'] ?? 0),
                    'd8_14' => (int) ($bl['d8_14'] ?? 0),
                    'd15_21' => (int) ($bl['d15_21'] ?? 0),
                    'd22_28' => (int) ($bl['d22_28'] ?? 0),
                    'd29_plus' => (int) ($bl['d29_plus'] ?? 0),
                    'sort_order' => $sort,
                ]);
            }

            $this->prune();

            return $snapshot->load('companies');
        } catch (Throwable $e) {
            $snapshot->update(['error' => substr($e->getMessage(), 0, 255)]);

            return $snapshot;
        }
    }

    /** Run a :month/:year count query, return [company_code => count]. */
    protected function countByCompany($conn, string $query, Carbon $month): array
    {
        $sql = str_replace([':month', ':year'], ['?', '?'], $query);
        $rows = $conn->select($sql, [$month->month, $month->year]);

        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r->company_code] = (int) $r->cnt;
        }

        return $out;
    }

    /** Run the backlog query, return [company_code => row-array]. */
    protected function backlogByCompany($conn, string $query): array
    {
        $rows = $conn->select($query);
        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r->company_code] = (array) $r;
        }

        return $out;
    }

    protected function prune(int $keep = 5): void
    {
        $ids = OtifSnapshot::whereNull('error')
            ->orderByDesc('captured_at')
            ->pluck('id')
            ->slice($keep);

        if ($ids->isNotEmpty()) {
            OtifSnapshot::whereIn('id', $ids)->delete();
        }
    }
}
