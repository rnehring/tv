@extends('layouts.admin')
@section('title', 'Monthly Slides')

@php $monthName = $months[$month]; @endphp

@section('content')
    {{-- Month picker + tabs --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.monthly.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="text-sm font-medium">Month</label>
            <select name="month" onchange="this.form.submit()"
                    class="rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                @foreach ($months as $num => $name)
                    <option value="{{ $num }}" {{ $num === $month ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </form>

        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm dark:border-gray-700 dark:bg-gray-800">
            @foreach (['birthdays' => '🎂 Birthdays', 'anniversaries' => '🎉 Anniversaries', 'backgrounds' => '🖼️ Backgrounds'] as $key => $label)
                <a href="{{ route('admin.monthly.index', ['tab' => $key, 'month' => $month]) }}"
                   class="rounded-md px-4 py-1.5 font-medium {{ $tab === $key ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($tab === 'birthdays')
        @include('admin.monthly._people', [
            'kind' => 'birthdays',
            'people' => $birthdays,
            'importRoute' => route('admin.monthly.birthdays.import', ['month' => $month]),
            'storeRoute' => route('admin.monthly.birthdays.store'),
            'anniversary' => false,
        ])
    @elseif ($tab === 'anniversaries')
        @include('admin.monthly._people', [
            'kind' => 'anniversaries',
            'people' => $anniversaries,
            'importRoute' => route('admin.monthly.anniversaries.import', ['month' => $month]),
            'storeRoute' => route('admin.monthly.anniversaries.store'),
            'anniversary' => true,
        ])
    @else
        @include('admin.monthly._backgrounds')
    @endif
@endsection
