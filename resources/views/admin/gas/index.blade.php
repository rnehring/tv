@extends('layouts.admin')
@section('title', 'Gas Cards')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="max-w-2xl text-sm text-gray-500 dark:text-gray-400">
            Monthly gift-card goal figures. The slideshow shows a thermometer for the most recent month that has goals entered.
            @if ($active)
                <span class="font-medium text-gray-700 dark:text-gray-300">Currently showing: {{ $active->monthLabel() }} {{ $active->year() }}.</span>
            @endif
        </p>
        <a href="{{ route('slideshow', ['preview' => 1]) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Preview Slideshow →</a>
    </div>

    {{-- Add a month --}}
    <form method="POST" action="{{ route('admin.gas.store') }}"
          class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        @csrf
        <h3 class="mb-3 text-base font-semibold">Add a Month</h3>
        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-7">
            <label class="text-xs text-gray-500">Month (YYYY-MM)
                <input type="text" name="year_month" value="{{ now()->format('Y-m') }}" placeholder="2026-08" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
            </label>
            @foreach (['goal50' => '$50 goal', 'goal100' => '$100 goal', 'goal150' => '$150 goal', 'goal200' => '$200 goal', 'achieved' => 'Achieved'] as $f => $lbl)
                <label class="text-xs text-gray-500">{{ $lbl }}
                    <input type="number" step="0.01" min="0" name="{{ $f }}" placeholder="0"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                </label>
            @endforeach
            <label class="text-xs text-gray-500">As of
                <input type="date" name="updated_on" value="{{ now()->toDateString() }}"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
            </label>
        </div>
        <button type="submit" class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Add Month</button>
    </form>

    {{-- Existing months, inline editable --}}
    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-3 py-3">Month</th>
                    <th class="px-3 py-3">$50</th>
                    <th class="px-3 py-3">$100</th>
                    <th class="px-3 py-3">$150</th>
                    <th class="px-3 py-3">$200</th>
                    <th class="px-3 py-3">Achieved</th>
                    <th class="px-3 py-3">As of</th>
                    <th class="px-3 py-3">Final</th>
                    <th class="px-3 py-3">Earned</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($charts as $c)
                    <form method="POST" action="{{ route('admin.gas.update', $c) }}" id="gas-{{ $c->id }}" class="hidden">@csrf @method('PUT')
                        <input type="hidden" name="year_month" value="{{ $c->year_month }}">
                    </form>
                    <tr class="{{ $active && $active->id === $c->id ? 'bg-blue-50/50 dark:bg-gray-700/40' : '' }}">
                        <td class="px-3 py-2 font-medium">{{ $c->monthLabel() }} {{ $c->year() }}</td>
                        @foreach (['goal50','goal100','goal150','goal200','achieved'] as $f)
                            <td class="px-3 py-2">
                                <input form="gas-{{ $c->id }}" type="number" step="0.01" min="0" name="{{ $f }}" value="{{ (float) $c->$f }}"
                                       class="w-28 rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                            </td>
                        @endforeach
                        <td class="px-3 py-2">
                            <input form="gas-{{ $c->id }}" type="date" name="updated_on" value="{{ optional($c->updated_on)->format('Y-m-d') }}"
                                   class="rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                        </td>
                        <td class="px-3 py-2 text-center">
                            <input form="gas-{{ $c->id }}" type="checkbox" name="finalized" value="1" {{ $c->finalized ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-blue-600">
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-500">{{ $c->earnedLevel() ? '$'.$c->earnedLevel() : '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <button form="gas-{{ $c->id }}" type="submit" class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Save</button>
                            <form method="POST" action="{{ route('admin.gas.destroy', $c) }}" data-confirm="Delete {{ $c->monthLabel() }} {{ $c->year() }}?" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="ml-1 rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">✕</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
