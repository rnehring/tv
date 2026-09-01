@php
    $isAnniv = $anniversary;
    $updateName = $isAnniv ? 'admin.monthly.anniversaries.update' : 'admin.monthly.birthdays.update';
    $destroyName = $isAnniv ? 'admin.monthly.anniversaries.destroy' : 'admin.monthly.birthdays.destroy';
    $paramKey = $isAnniv ? 'anniversary' : 'birthday';
@endphp

{{-- Import + Add, side by side --}}
<div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-2 text-base font-semibold">Import CSV</h3>
        <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
            Drop in the ADP payroll export. Columns:
            @if ($isAnniv)
                <span class="font-medium">Month, Company Code, Home Department, Length of Service, Payroll Name (Last, First), Hire/Rehire Date.</span>
            @else
                <span class="font-medium">[Company Code], Month, Birth Day, Home Department, Payroll Name (Last, First).</span>
            @endif
            Rows are matched on name + date, so re-importing updates rather than duplicates.
        </p>
        <form method="POST" action="{{ $importRoute }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
            @csrf
            <input type="file" name="csv" accept=".csv,text/csv" required
                   class="min-w-0 flex-1 cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-500 file:me-4 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-gray-200 file:px-5 file:py-2.5 file:font-medium file:text-gray-800 hover:file:bg-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:file:bg-gray-600 dark:file:text-gray-100">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Upload &amp; Import</button>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-3 text-base font-semibold">Add Someone to {{ $monthName }}</h3>
        <form method="POST" action="{{ $storeRoute }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <label class="text-xs text-gray-500">First name
                <input type="text" name="first_name" required
                       class="mt-1 block w-40 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
            <label class="text-xs text-gray-500">Last name
                <input type="text" name="last_name" required
                       class="mt-1 block w-40 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
            <label class="text-xs text-gray-500">Day
                <input type="number" name="day" min="1" max="31" required
                       class="mt-1 block w-20 rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
            @if ($isAnniv)
                <label class="text-xs text-gray-500">Hire date
                    <input type="date" name="hire_date"
                           class="mt-1 block rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
            @endif
            <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">+ Add</button>
        </form>
    </div>
</div>

<hr class="my-6 border-gray-200 dark:border-gray-700">

{{-- People listing, full width --}}
<div class="mb-3 flex items-center justify-between">
    <h3 class="text-base font-semibold">{{ $monthName }} {{ $isAnniv ? 'Anniversaries' : 'Birthdays' }} <span class="text-gray-400">({{ $people->count() }})</span></h3>
</div>

{{-- Row edit forms live outside the table (valid HTML); inputs bind via form="row-id". --}}
@foreach ($people as $person)
    <form method="POST" action="{{ route($updateName, [$paramKey => $person]) }}" id="row-{{ $person->id }}" class="hidden">
        @csrf @method('PUT')
        <input type="hidden" name="month" value="{{ $person->month }}">
    </form>
@endforeach

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <table class="w-full text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th class="px-3 py-2.5">Day</th>
                <th class="px-3 py-2.5">First</th>
                <th class="px-3 py-2.5">Last</th>
                @if ($isAnniv)<th class="px-3 py-2.5">Hire date</th><th class="px-3 py-2.5">Years</th>@endif
                <th class="px-3 py-2.5 text-right">Save</th>
                <th class="px-3 py-2.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($people as $person)
                <tr>
                    <td class="px-3 py-2">
                        <input form="row-{{ $person->id }}" type="number" name="day" min="1" max="31" value="{{ $person->day }}"
                               class="w-16 rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </td>
                    <td class="px-3 py-2">
                        <input form="row-{{ $person->id }}" type="text" name="first_name" value="{{ $person->first_name }}"
                               class="w-full rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </td>
                    <td class="px-3 py-2">
                        <input form="row-{{ $person->id }}" type="text" name="last_name" value="{{ $person->last_name }}"
                               class="w-full rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </td>
                    @if ($isAnniv)
                        <td class="px-3 py-2">
                            <input form="row-{{ $person->id }}" type="date" name="hire_date" value="{{ optional($person->hire_date)->format('Y-m-d') }}"
                                   class="rounded border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                        </td>
                        <td class="px-3 py-2 text-gray-500">{{ $person->years_label }}</td>
                    @endif
                    <td class="px-3 py-2 text-right">
                        <button form="row-{{ $person->id }}" type="submit"
                                class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Save</button>
                    </td>
                    <td class="px-3 py-2 text-right">
                        <form method="POST" action="{{ route($destroyName, [$paramKey => $person]) }}" data-confirm="Remove {{ $person->full_name }}?">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-gray-700">✕</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $isAnniv ? 6 : 5 }}" class="px-4 py-10 text-center text-gray-400">
                    Nobody for {{ $monthName }} yet — import a CSV or add someone. The {{ $isAnniv ? 'anniversary' : 'birthday' }} slide hides itself while this is empty.
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
