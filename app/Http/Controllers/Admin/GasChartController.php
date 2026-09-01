<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GasChart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GasChartController extends Controller
{
    public function index(): View
    {
        // Make sure the current month exists so it's easy to fill in.
        GasChart::firstOrCreate(
            ['year_month' => now()->format('Y-m')],
            ['updated_on' => now()->toDateString()]
        );

        return view('admin.gas.index', [
            'charts' => GasChart::orderByDesc('year_month')->get(),
            'active' => GasChart::latestWithGoals(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        GasChart::create($data);

        return back()->with('status', 'Gas card month added.');
    }

    public function update(Request $request, GasChart $gasChart): RedirectResponse
    {
        $data = $this->validated($request, $gasChart->id);
        $gasChart->update($data);

        return back()->with('status', $gasChart->monthLabel().' '.$gasChart->year().' saved.');
    }

    public function destroy(GasChart $gasChart): RedirectResponse
    {
        $gasChart->delete();

        return back()->with('status', 'Month removed.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'year_month' => [
                'required', 'regex:/^\d{4}-\d{2}$/',
                'unique:gas_charts,year_month'.($ignoreId ? ','.$ignoreId : ''),
            ],
            'updated_on' => ['nullable', 'date'],
            'goal50' => ['nullable', 'numeric', 'min:0'],
            'goal100' => ['nullable', 'numeric', 'min:0'],
            'goal150' => ['nullable', 'numeric', 'min:0'],
            'goal200' => ['nullable', 'numeric', 'min:0'],
            'achieved' => ['nullable', 'numeric', 'min:0'],
            'finalized' => ['nullable', 'boolean'],
        ]);

        foreach (['goal50', 'goal100', 'goal150', 'goal200', 'achieved'] as $f) {
            $data[$f] = $data[$f] ?? 0;
        }
        $data['finalized'] = $request->boolean('finalized');

        return $data;
    }
}
