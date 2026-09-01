@extends('layouts.admin')
@section('title', 'Locations')

@section('content')
    {{-- Add a location (full width, blue-tinted so it stands apart) --}}
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800/60 dark:bg-blue-950/40">
        <h3 class="mb-4 text-base font-semibold">Add a location</h3>
        <form method="POST" action="{{ route('admin.locations.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-3 md:grid-cols-3">
                <label class="text-sm">Name
                    <input type="text" name="name" placeholder="e.g. Houston" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                </label>
                <label class="text-sm">Description <span class="text-gray-400">(optional)</span>
                    <input type="text" name="description"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                </label>
                <label class="text-sm">IP prefix
                    <input type="text" name="ip_pattern" placeholder="e.g. 192.168."
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                    <span class="mt-1 block text-xs text-gray-400">A TV whose IP starts with this prefix auto-selects this zone.</span>
                </label>
            </div>
            <div class="flex flex-wrap items-center gap-5">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-gray-300 text-blue-600"> Default zone</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-gray-300 text-blue-600"> Active</label>
                <button type="submit" class="ml-auto rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Add location</button>
            </div>
        </form>
    </div>

    <hr class="my-6 border-gray-200 dark:border-gray-700">

    <h3 class="mb-4 text-base font-semibold">Existing locations</h3>
    <div class="grid gap-4 xl:grid-cols-2">
        @forelse ($locations as $loc)
            <div>
                <form method="POST" action="{{ route('admin.locations.update', $loc) }}"
                      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    @csrf @method('PUT')
                    <div class="mb-3 flex items-center justify-between">
                        <code class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700">{{ url('/?location='.$loc->slug) }}</code>
                        @if ($loc->is_default)<span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">Default</span>@endif
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">Name
                            <input type="text" name="name" value="{{ $loc->name }}" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <label class="text-sm">IP prefix
                            <input type="text" name="ip_pattern" value="{{ $loc->ip_pattern }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <label class="text-sm sm:col-span-2">Description
                            <input type="text" name="description" value="{{ $loc->description }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" {{ $loc->is_default ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600"> Default</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" {{ $loc->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600"> Active</label>
                        <label class="flex items-center gap-2 text-sm">Order
                            <input type="number" name="sort_order" value="{{ $loc->sort_order }}" min="0"
                                   class="w-20 rounded-lg border border-gray-300 bg-gray-50 p-1.5 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <button type="submit" class="ml-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.locations.destroy', $loc) }}" data-confirm="Delete {{ $loc->name }}? Slides targeted only to it will fall back to showing everywhere." class="mt-1 text-right">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:underline">Delete this location</button>
                </form>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-400 dark:border-gray-600 xl:col-span-2">
                No locations yet. Add one above, or leave it empty to run a single shared slideshow.
            </div>
        @endforelse
    </div>
@endsection
