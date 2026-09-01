@php
    /** @var \App\Models\OtifSnapshot $snapshot */
    $snapshot = $slide['snapshot'];
    $companies = $snapshot->companies->filter(fn ($c) => $c->hasShipments())->values();
    $all = $snapshot->allOrders();
    $target = (float) config('otif.target_percent', 95);

    $backlogOpen = $snapshot->companies->sum('open_orders');
    $backlogPd = $snapshot->companies->sum('past_due');

    $pct = fn ($n) => number_format($n, 1);
    $colorFor = function ($p) use ($target) {
        if ($p === null) return '#94a3b8';
        if ($p >= $target) return '#22c55e';
        if ($p >= 90) return '#f59e0b';
        return '#f87171';
    };
    $reportMonth = ($snapshot->captured_at->day <= 3 ? $snapshot->captured_at->copy()->subMonthNoOverflow() : $snapshot->captured_at)->format('F Y');
@endphp
<div class="otif">
    <div class="head">
        <div>
            <h1>OTIF <span>· On-Time In Full</span></h1>
            <div class="sub">% of orders shipped on or before the customer's required date · {{ $reportMonth }}</div>
        </div>
        <div class="cap">
            As of<br><b>{{ $snapshot->captured_at->format('M j, Y') }}</b>
            @if ($snapshot->isStale())<br><span class="stale">⚠ data may be delayed</span>@endif
        </div>
    </div>

    <div class="top">
        <div class="allcard">
            <div>
                <div class="k">All Orders</div>
                <div class="big">{{ $all['otif'] !== null ? $pct($all['otif']).'%' : '—' }}</div>
            </div>
            <div class="meta">
                {{ number_format($all['shipped_on_time']) }} of {{ number_format($all['shipped_total']) }} orders on time
                @if ($all['delta'] !== null)
                    <br><span class="delta {{ $all['delta'] >= 0 ? 'up' : 'down' }}">{{ $all['delta'] >= 0 ? '▲' : '▼' }} {{ $pct(abs($all['delta'])) }} pts</span> vs last month
                @endif
            </div>
        </div>
        <div class="legend">
            <span><span class="sw" style="background:#22c55e"></span>≥ {{ (int) $target }}%</span>
            <span><span class="sw" style="background:#f59e0b"></span>90–{{ (int) $target }}%</span>
            <span><span class="sw" style="background:#f87171"></span>&lt; 90%</span>
            <span><span class="sw" style="background:#fff"></span>{{ (int) $target }}% target</span>
        </div>
    </div>

    <div class="rows">
        @foreach ($companies as $c)
            @php $p = $c->otifPercent(); $d = $c->deltaPoints(); $col = $colorFor($p); @endphp
            <div class="row">
                <div class="name">{{ $c->company_name }}</div>
                <div class="track">
                    <div class="fill" style="width: {{ max(2, min(100, $p ?? 0)) }}%; background: {{ $col }};"></div>
                    <div class="target" style="left: {{ $target }}%;"></div>
                </div>
                <div class="pctv" style="color: {{ $col }};">{{ $p !== null ? $pct($p).'%' : '—' }}</div>
                <div class="dl {{ ($d ?? 0) >= 0 ? 'up' : 'down' }}">{{ $d !== null ? (($d >= 0 ? '▲' : '▼').' '.$pct(abs($d)).' pts') : '' }}</div>
                <div class="cnt">{{ $c->shipped_on_time }}/{{ $c->shipped_total }}</div>
            </div>
        @endforeach
    </div>

    <div class="foot">
        <div>@if($backlogOpen > 0)Open backlog: <b>{{ number_format($backlogOpen) }}</b> orders · <b class="pd">{{ number_format($backlogPd) }}</b> past due @endif</div>
        <div>On-time shipping drives the Gift Card Program</div>
    </div>
</div>
