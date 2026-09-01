@extends('layouts.admin')
@section('title', 'Static Slides')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded graphics and embedded pages shown in the rotation.</p>
        <a href="{{ route('admin.slides.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ New Slide</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Slide</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Duration</th>
                    <th class="px-4 py-3">Shows</th>
                    <th class="px-4 py-3">Locations</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($slides as $slide)
                    <tr>
                        <td class="px-4 py-3 text-gray-400">{{ $slide->sort_order }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($slide->type === 'image' && $slide->image_url)
                                    <img src="{{ $slide->image_url }}" alt="" class="h-10 w-16 rounded object-cover">
                                @else
                                    <span class="flex h-10 w-16 items-center justify-center rounded bg-gray-100 text-lg dark:bg-gray-700">🔗</span>
                                @endif
                                <div>
                                    <div class="font-medium">{{ $slide->name }}</div>
                                    @if ($slide->caption)<div class="text-xs text-gray-400">{{ str(strip_tags($slide->caption))->limit(40) }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize">{{ $slide->type }}</td>
                        <td class="px-4 py-3">{{ number_format($slide->duration_ms / 1000, 1) }}s</td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ $slide->starts_on?->format('M j, Y') ?? 'always' }}
                            @if ($slide->ends_on) → {{ $slide->ends_on->format('M j, Y') }} @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            {{ $slide->locations->isEmpty() ? 'Everywhere' : $slide->locations->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.slides.toggle', $slide) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $slide->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $slide->is_active ? 'On' : 'Off' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.slides.edit', $slide) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Edit</a>
                                <form method="POST" action="{{ route('admin.slides.destroy', $slide) }}" data-confirm="Delete “{{ $slide->name }}”?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:hover:bg-gray-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No static slides yet. <a href="{{ route('admin.slides.create') }}" class="text-blue-600 hover:underline">Add one →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Generated slides: display settings only --}}
    <h2 class="mb-3 mt-8 text-lg font-semibold">Generated Monthly Slides</h2>
    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
        These render live from their data — birthdays &amp; anniversaries from <a href="{{ route('admin.monthly.index') }}" class="text-blue-600 hover:underline">Monthly slides</a>, the gift-card thermometer from <a href="{{ route('admin.gas.index') }}" class="text-blue-600 hover:underline">Gas cards</a>, and the on-time scorecard from <a href="{{ route('admin.otif.index') }}" class="text-blue-600 hover:underline">OTIF backlog</a>. Toggle each on/off, set its duration, order, and locations here; a slide hides itself automatically when it has no data.
    </p>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($generated as $slide)
            <form method="POST" action="{{ route('admin.slides.update', $slide) }}"
                  class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                @csrf @method('PUT')
                @php $genIcon = ['birthday' => '🎂', 'anniversary' => '🎉', 'gas' => '⛽', 'otif' => '📦'][$slide->type] ?? '📄'; @endphp
                <div class="mb-3 flex items-center gap-2 text-base font-semibold">
                    <span>{{ $genIcon }}</span>{{ $slide->name }}
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="text-sm">Duration
                        <input type="number" step="500" min="1000" max="120000" name="duration_ms" value="{{ $slide->duration_ms }}"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                        <span class="text-xs text-gray-400">milliseconds</span>
                    </label>
                    <label class="text-sm">Order
                        <input type="number" min="0" name="sort_order" value="{{ $slide->sort_order }}"
                               class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                    </label>
                </div>
                <label class="mt-3 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Active
                </label>
                <div class="mt-3">
                    <span class="text-sm font-medium">Locations</span>
                    <div class="mt-1 flex flex-wrap gap-3">
                        @foreach ($locations as $loc)
                            <label class="flex items-center gap-1.5 text-sm">
                                <input type="checkbox" name="locations[]" value="{{ $loc->id }}"
                                       {{ $slide->locations->contains($loc->id) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                {{ $loc->name }}
                            </label>
                        @endforeach
                        <span class="text-xs text-gray-400">(none = everywhere)</span>
                    </div>
                </div>
                <button type="submit" class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save</button>
            </form>
        @endforeach
    </div>
@endsection
