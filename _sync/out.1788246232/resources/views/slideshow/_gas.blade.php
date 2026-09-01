@php
    /** @var \App\Models\GasChart $chart */
    $chart = $slide['chart'];
    $tiers = $chart->tiers();
    $top = $chart->topGoal();
    $achieved = (float) $chart->achieved;
    $earned = $chart->earnedLevel();
    $ahead = $chart->isAheadOfPace();
    $paceAmt = $chart->paceAmount();
    $pct = $chart->percentOfTop();
    $fill = $ahead ? '#16a34a' : '#ea580c';
    $lastTier = array_key_last($tiers);
    $money = fn ($n) => '$'.number_format($n);
@endphp
<div class="gas">
    <div class="head">
        <div>
            <h1>{{ $chart->monthLabel() }} <span>Gift Card Goal</span></h1>
            <div class="sub">Company sales toward the monthly team gift-card levels</div>
        </div>
        <div class="updated">
            Last updated<br><b>{{ optional($chart->updated_on)->format('M j, Y') ?? '—' }}</b>
            @if ($chart->finalized)<br><span class="final">FINALIZED</span>@endif
        </div>
    </div>

    <div class="body">
        <div class="left">
            <div class="bigpct" style="color: {{ $fill }};">{{ rtrim(rtrim(number_format($pct, 1), '0'), '.') }}<span>%</span></div>
            <div class="bigsub">of the ${{ $lastTier }} goal ({{ $money($top) }})</div>
            <div class="salesline">{{ $money($achieved) }} in sales to date</div>
            <div class="bar">
                <div class="fillbar" style="width: {{ max(2, min(100, $pct)) }}%; background: linear-gradient(90deg, {{ $fill }}, {{ $fill }}cc);"></div>
                <div class="pacetick" style="left: {{ max(0, min(100, $top > 0 ? $paceAmt / $top * 100 : 0)) }}%;"></div>
            </div>
            <div class="barlabels"><span>$0</span><span>{{ $money($top) }}</span></div>
            <div class="pacenote">{{ $ahead ? '▲ Ahead of pace by ' : '▼ Behind pace by ' }}{{ $money(round(abs($achieved - $paceAmt))) }}</div>
        </div>

        <div class="right">
            <div class="earned {{ $earned ? '' : 'none' }}">
                <div class="k">Currently earned</div>
                <div class="v">{{ $earned ? '$'.$earned.' Gift Card!' : 'No tier reached yet' }}</div>
            </div>
            <div class="ladder">
                @foreach ($tiers as $t => $v)
                    @php $reached = $achieved >= $v; @endphp
                    <div class="row {{ $reached ? 'reached' : '' }} {{ $t === $earned ? 'current' : '' }}">
                        <span class="chip">${{ $t }}</span>
                        <span class="amt">{{ $money($v) }}</span>
                        <span class="stat">{{ $reached ? '' : $money($v - $achieved).' to go' }}</span>
                    </div>
                @endforeach
            </div>
            <div class="foot">
                <div class="card"><div class="k">Actual daily so far</div><div class="v">{{ $money(round($chart->dailyActual())) }}</div></div>
                <div class="card"><div class="k">Daily needed for ${{ $lastTier }}</div><div class="v">{{ $money(round($chart->dailyTarget($lastTier))) }}</div></div>
            </div>
        </div>
    </div>
</div>
