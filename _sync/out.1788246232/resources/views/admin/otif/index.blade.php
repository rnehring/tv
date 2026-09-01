@extends('layouts.admin')
@section('title', 'OTIF Backlog')

@section('content')
    @php $isEpicor = $source === 'epicor'; @endphp

    <div class="mb-6 grid gap-5 lg:grid-cols-3">
        {{-- Status --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-2 text-sm text-gray-500 dark:text-gray-400">Data Source</div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                    {{ $isEpicor ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' }}">
                    {{ $isEpicor ? 'Live · Epicor' : 'Sample data' }}
                </span>
            </div>
            <div class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                @if ($snapshot)
                    Last snapshot: <b>{{ $snapshot->captured_at->diffForHumans() }}</b>
                    ({{ $snapshot->captured_at->format('M j, g:i A') }})
                    @if ($snapshot->isStale())<span class="text-amber-600"> · stale</span>@endif
                @else
                    No snapshot yet.
                @endif
            </div>
            <form method="POST" action="{{ route('admin.otif.refresh') }}" class="mt-4">
                @csrf
                <button type="submit"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-white {{ $isEpicor ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed' }}"
                        {{ $isEpicor ? '' : 'disabled title=Enable-epicor-first' }}>
                    Refresh Now
                </button>
                @unless ($isEpicor)
                    <p class="mt-2 text-xs text-gray-400">Set <code>OTIF_SOURCE=epicor</code> in <code>.env</code> to enable live pulls.</p>
                @endunless
            </form>
        </div>

        {{-- Business units --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
            <div class="mb-2 text-sm text-gray-500 dark:text-gray-400">Business Units (edit in <code>config/otif.php</code>)</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($companies as $c)
                    <span class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-600">
                        {{ $c['name'] }} <code class="text-xs text-gray-400">{{ $c['code'] }}</code>
                    </span>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">Refreshes automatically every {{ $refreshMinutes }} minutes when live (requires the Laravel scheduler running).</p>
        </div>
    </div>

    {{-- Latest snapshot table --}}
    @if ($snapshot && $snapshot->companies->isNotEmpty())
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Business unit</th>
                        <th class="px-4 py-3">On-Time %</th>
                        <th class="px-4 py-3">On-time / total shipped</th>
                        <th class="px-4 py-3">Δ vs last month</th>
                        <th class="px-4 py-3">Open backlog</th>
                        <th class="px-4 py-3">Past due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($snapshot->companies as $c)
                        @php $p = $c->otifPercent(); $d = $c->deltaPoints(); @endphp
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $c->company_name }}</td>
                            <td class="px-4 py-3 font-semibold {{ $p === null ? 'text-gray-400' : ($p >= 95 ? 'text-green-600' : ($p >= 90 ? 'text-amber-600' : 'text-red-600')) }}">
                                {{ $p !== null ? number_format($p, 1).'%' : '—' }}
                            </td>
                            <td class="px-4 py-3">{{ $c->shipped_on_time }} / {{ $c->shipped_total }}</td>
                            <td class="px-4 py-3 {{ ($d ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $d !== null ? (($d >= 0 ? '+' : '').number_format($d, 1).' pts') : '—' }}
                            </td>
                            <td class="px-4 py-3">{{ $c->open_orders }}</td>
                            <td class="px-4 py-3 text-red-600">{{ $c->past_due }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Setup help --}}
    <details class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <summary class="cursor-pointer text-sm font-semibold">Wiring Up the Live Epicor Connection</summary>
        <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
            <p>The app reads Epicor read-only through a dedicated connection. On the host that runs this app (which must be able to reach the Epicor SQL Server and have the <code>sqlsrv</code> or <code>pdo_dblib</code> PHP driver), set these in <code>.env</code>:</p>
            <pre class="overflow-x-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100">OTIF_SOURCE=epicor
EPICOR_DSN=EpicorMSSQL          # if using a system ODBC DSN
# — or host/port instead of a DSN —
EPICOR_HOST=your-sql-host
EPICOR_PORT=1433
EPICOR_DATABASE=your-epicor-db
EPICOR_USERNAME=readonly_user
EPICOR_PASSWORD=********</pre>
            <p>Confirm the business-unit codes in <code>config/otif.php</code> match your Epicor <code>OrderHed.Company</code> values, then click <b>Refresh Now</b>. For automatic updates, run the Laravel scheduler on the host (<code>* * * * * php artisan schedule:run</code>).</p>
        </div>
    </details>
@endsection
